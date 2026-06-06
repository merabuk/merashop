<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeCodeException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute\CreateAttributeController;
use App\Tests\Catalog\Support\Traits\AttributeFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateAttributeControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use AttributeFactoryTrait;
    use BaseUriTrait;

    private const string ROUTE_NAME = CreateAttributeController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_POST;

    protected function tearDown(): void
    {
        $this->clearIdentity();

        parent::tearDown();
    }

    public function testItReturnsForbiddenForGuests(): void
    {
        $client = self::createClient();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItForbiddenForRegularUser(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws InvalidAttributeCodeException
     */
    #[DataProvider('validAttributeProvider')]
    public function testItSuccessfullyCreatesAttribute(array $payload): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: $payload
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('message', $data);
        self::assertStringContainsString('Attribute was successfully created', $data['message']);

        $exists = $this->getReadRepository()->existsByCode(Code::fromString($payload['code']));
        self::assertTrue($exists, 'Attribute was not saved to database');
    }

    public static function validAttributeProvider(): iterable
    {
        yield 'without options' => [
            'payload' => [
                'code' => 'brand-name',
                'type' => TypeEnum::String->value,
                'translations' => self::validAttributeTranslations(),
            ],
        ];
        yield 'with options' => [
            'payload' => [
                'code' => 'brand-name',
                'type' => TypeEnum::Select->value,
                'translations' => self::validAttributeTranslations(),
                'options' => [
                    [
                        'code' => 'brand-name-1',
                        'translations' => self::getAttributeOptionTranslations(),
                        'isActive' => true,
                    ],
                    [
                        'code' => 'brand-name-2',
                        'translations' => self::getAttributeOptionTranslations(),
                        'isActive' => false,
                    ],
                ],
            ],
        ];
    }

    public function testItReturns409WhenCodeAlreadyExists(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $existingAttribute = $this->getAttributeFixture()->create();

        $payload = [
            'code' => $existingAttribute->getCode()->value(),
            'type' => TypeEnum::String->value,
            'translations' => self::validAttributeTranslations(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: $payload
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);

        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: ErrorCodeEnum::AttributeAlreadyExists->value,
            expectedContainMessage: sprintf('Attribute with code "%s" already exists', $payload['code'])
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
            uri: $this->getUrl(),
            payload: $payload
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
        yield 'empty options' => [
            'payload' => [
                ...$payload,
                'type' => TypeEnum::Select->value,
            ],
            'expectedErrorFields' => ['options'],
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
        yield 'missing and invalid option translation value' => [
            'payload' => [
                ...$payload,
                'type' => TypeEnum::Select->value,
                'options' => [
                    [
                        ...$optionPayload,
                        'translations' => [
                            'en' => ['value' => ''],
                            'xx' => ['value' => 'Option'],
                        ],
                    ],
                ],
            ],
            'expectedErrorFields' => ['options[0].translations', 'options[0].translations[xx]'],
        ];
        yield 'invalid option en value translation value' => [
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
