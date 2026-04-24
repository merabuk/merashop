<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute\UpdateAttributeController;
use App\Shared\Domain\Enum\ErrorCodeEnum as SharedErrorCodeEnum;
use App\Tests\Catalog\Support\AttributeOptionMother;
use App\Tests\Catalog\Support\Traits\AttributeFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateAttributeControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use AttributeFactoryTrait;
    use BaseUriTrait;

    private const string ROUTE_NAME = UpdateAttributeController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_PUT;

    protected function tearDown(): void
    {
        $this->clearIdentity();

        parent::tearDown();
    }

    public function testItReturnsForbiddenForGuests(): void
    {
        $client = self::createClient();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['ulid' => $this->getAttributeMother()::DEFAULT_ULID]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItForbiddenForRegularUser(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['ulid' => $this->getAttributeMother()::DEFAULT_ULID]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    #[DataProvider('validAttributeProvider')]
    public function testItSuccessfullyUpdatesAttribute(
        TypeEnum $type,
        array $payload,
        array $options = [],
        int $expectedOptionsCount = 0,
    ): void {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute = $this->getAttributeFixture()->create(type: $type, options: $options);

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['ulid' => $attribute->getUlid()->value()]),
            payload: [
                'version' => $attribute->getVersion()->value(),
                ...$payload,
            ],
        );

        $this->assertResponseIsSuccessful();

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('message', $data);
        self::assertStringContainsString('Attribute was successfully updated', $data['message']);

        $updated = $this->getReadRepository()->findById($attribute->getId());
        self::assertSame($payload['code'], $updated->getCode()->value());
        self::assertSame($payload['type'], $updated->getType()->value()->value);
        self::assertCount($expectedOptionsCount, $updated->getOptions());
    }

    public static function validAttributeProvider(): iterable
    {
        yield 'without options with type change' => [
            'type' => TypeEnum::String,
            'payload' => [
                'code' => 'brand-updated',
                'type' => TypeEnum::Text->value,
                'translations' => self::validAttributeTranslations(),
            ],
        ];
        yield 'with options' => [
            'type' => TypeEnum::Select,
            'payload' => [
                'code' => 'brand-updated',
                'type' => TypeEnum::Select->value,
                'translations' => self::validAttributeTranslations(),
                'options' => [
                    [
                        'ulid' => '01KMDEC4Z9NSK4YPEW8NG5068T',
                        'code' => 'updated-existing-option',
                        'translations' => self::getAttributeOptionTranslations(),
                        'isActive' => true,
                    ],
                    [
                        'code' => 'added-new-option',
                        'translations' => self::getAttributeOptionTranslations(),
                        'isActive' => true,
                    ],
                ],
            ],
            'options' => [
                AttributeOptionMother::createWithData(
                    ulid: '01KMDEC4Z9NSK4YPEW8NG5068T',
                    code: 'for-update',
                ),
                AttributeOptionMother::createWithData(
                    ulid: '01KMGY62KTY8BJ9J8NHMXHKF4P',
                    code: 'for-deactivation',
                ),
            ],
            'expectedOptionsCount' => 3,
        ];
    }

    public function testIfReturns404WhenAttributeNotFound(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $payload = [
            'code' => 'not-found',
            'type' => TypeEnum::Text->value,
            'translations' => self::validAttributeTranslations(),
            'version' => 1,
        ];

        $ulid = $this->getAttributeMother()::DEFAULT_ULID;

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['ulid' => $ulid]),
            payload: $payload,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::AttributeNotFound->value,
            expectedContainMessage: sprintf('Attribute with ulid "%s" not found', $ulid),
        );
    }

    public function testItReturns409WhenTypeCanNotBeChanged(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute = $this->getAttributeFixture()->create(type: TypeEnum::Integer);

        $payload = [
            'code' => $attribute->getCode()->value(),
            'version' => $attribute->getVersion()->value(),
            'type' => TypeEnum::String->value,
            'translations' => self::validAttributeTranslations(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['ulid' => $attribute->getUlid()->value()]),
            payload: $payload,
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::AttributeTypeCanNotBeChanged->value,
            expectedContainMessage: 'Attribute type cannot be changed',
        );
    }

    public function testItReturns409OnConcurrencyError(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute = $this->getAttributeFixture()->create(code: 'old', type: TypeEnum::String);

        $payload = [
            'code' => 'new',
            'type' => $attribute->getType()->value()->value,
            'translations' => self::validAttributeTranslations(),
            'version' => $attribute->getVersion()->value() + 1,
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['ulid' => $attribute->getUlid()->value()]),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: SharedErrorCodeEnum::ConcurrencyError->value,
            expectedContainMessage: 'The "Attribute" has been already modified. Please refresh the page and try again'
        );
    }

    public function testItReturns409WhenCodeAlreadyExists(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $existingAttribute = $this->getAttributeFixture()->create(code: 'existing', type: TypeEnum::String);
        $attribute = $this->getAttributeFixture()->create(code: 'old', type: TypeEnum::String);

        $payload = [
            'code' => $existingAttribute->getCode()->value(),
            'type' => $attribute->getType()->value()->value,
            'translations' => self::validAttributeTranslations(),
            'version' => $attribute->getVersion()->value(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['ulid' => $attribute->getUlid()->value()]),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::AttributeAlreadyExists->value,
            expectedContainMessage: sprintf('Attribute with code "%s" already exists', $payload['code'])
        );
    }

    public function testItReturns409WhenGivenAttributeOptionUlidNotFound(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $option = AttributeOptionMother::createWithData();
        $attribute = $this->getAttributeFixture()->create(type: TypeEnum::Select, options: [$option]);

        $payload = [
            'code' => $attribute->getCode()->value(),
            'type' => $attribute->getType()->value()->value,
            'translations' => self::validAttributeTranslations(),
            'version' => $attribute->getVersion()->value(),
            'options' => [
                [
                    'ulid' => '01KMDEC4Z9NSK4YPEW8NG5068T',
                    'code' => $option->getCode()->value(),
                    'translations' => $option->getTranslations()->toArray(),
                    'isActive' => $option->isActive()->value(),
                ],
            ],
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['ulid' => $attribute->getUlid()->value()]),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::AttributeOptionNotFound->value,
            expectedContainMessage: sprintf('Attribute option with ulid "%s" not found', $payload['options'][0]['ulid'])
        );
    }

    #[DataProvider('invalidAttributeProvider')]
    public function testItReturns422OnInvalidData(array $payload, array $expectedErrorFields): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['ulid' => $this->getAttributeMother()::DEFAULT_ULID]),
            payload: $payload,
        );

        $this->assertResponseIsUnprocessable();

        $data = $this->getResponseData($client);

        $this->assertValidationErrors(responseData: $data, expectedErrorFields: $expectedErrorFields);
    }

    public static function invalidAttributeProvider(): iterable
    {
        $payload = [
            'code' => 'brand',
            'type' => TypeEnum::String->value,
            'translations' => self::validAttributeTranslations(),
            'version' => 1,
        ];
        $optionPayload = [
            'code' => 'brand-name-1',
            'translations' => self::getAttributeOptionTranslations(),
            'isActive' => true,
        ];

        yield 'empty code' => [
            'payload' => [
                ...$payload,
                'code' => '',
            ],
            'expectedErrorFields' => ['code'],
        ];
        yield 'invalid type' => [
            'payload' => [
                ...$payload,
                'type' => 'wrong_type',
            ],
            'expectedErrorFields' => ['type'],
        ];
        yield 'invalid locale and missed required locales' => [
            'payload' => [
                ...$payload,
                'translations' => [
                    'xx' => ['name' => 'Name'],
                ],
            ],
            'expectedErrorFields' => ['translations', 'translations[xx]'],
        ];
        yield 'invalid attribute translation name' => [
            'payload' => [
                ...$payload,
                'translations' => [
                    ...self::validAttributeTranslations(),
                    'en' => ['name' => ''],
                ],
            ],
            'expectedErrorFields' => ['translations[en].name'],
        ];
        yield 'invalid version' => [
            'payload' => [
                ...$payload,
                'version' => -1,
            ],
            'expectedErrorFields' => ['version'],
        ];
        yield 'empty options' => [
            'payload' => [
                ...$payload,
                'type' => TypeEnum::Select->value,
            ],
            'expectedErrorFields' => ['options'],
        ];
        yield 'invalid option ulid' => [
            'payload' => [
                ...$payload,
                'type' => TypeEnum::Select->value,
                'options' => [
                    [
                        ...$optionPayload,
                        'ulid' => 'ulid',
                    ],
                ],
            ],
            'expectedErrorFields' => ['options[0].ulid'],
        ];
        yield 'invalid option code' => [
            'payload' => [
                ...$payload,
                'type' => TypeEnum::Select->value,
                'options' => [
                    [
                        ...$optionPayload,
                        'code' => '',
                    ],
                ],
            ],
            'expectedErrorFields' => ['options[0].code'],
        ];
        yield 'invalid option locale and missed required locales' => [
            'payload' => [
                ...$payload,
                'type' => TypeEnum::Select->value,
                'options' => [
                    [
                        ...$optionPayload,
                        'translations' => [
                            'xx' => ['value' => 'Option'],
                        ],
                    ],
                ],
            ],
            'expectedErrorFields' => ['options[0].translations', 'options[0].translations[xx]'],
        ];
        yield 'invalid option translation value' => [
            'payload' => [
                ...$payload,
                'type' => TypeEnum::Select->value,
                'options' => [
                    [
                        ...$optionPayload,
                        'translations' => [
                            ...self::getAttributeOptionTranslations(),
                            'en' => ['value' => ''],
                        ],
                    ],
                ],
            ],
            'expectedErrorFields' => ['options[0].translations[en].value'],
        ];
        yield 'invalid option active' => [
            'payload' => [
                ...$payload,
                'type' => TypeEnum::Select->value,
                'options' => [
                    [
                        ...$optionPayload,
                        'isActive' => 'invalid_value',
                    ],
                ],
            ],
            'expectedErrorFields' => ['options[0].isActive'],
        ];
        yield 'invalid option base ratio' => [
            'payload' => [
                ...$payload,
                'type' => TypeEnum::Dimension->value,
                'options' => [
                    $optionPayload,
                ],
            ],
            'expectedErrorFields' => ['options[0].baseRatio'],
        ];
    }

    public function testItReturns404OnInvalidRouteParameterFormat(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: '/admin/api/v1/catalog/attributes/invalid-ulid'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: SharedErrorCodeEnum::NotFound->value,
            expectedContainMessage: 'No route found for'
        );
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }

    private function getReadRepository(): AttributeReadRepositoryInterface
    {
        return self::getContainer()->get(AttributeReadRepositoryInterface::class);
    }

    private static function validAttributeTranslations(): array
    {
        return [
            'en' => ['name' => 'Brand'],
            'uk' => ['name' => 'Бренд'],
        ];
    }

    private static function getAttributeOptionTranslations(): array
    {
        return [
            'en' => ['value' => 'Option'],
            'uk' => ['value' => 'Опція'],
        ];
    }
}
