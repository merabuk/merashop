<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute\UpdateAttributeController;
use App\Shared\Domain\Enum\ErrorCodeEnum as SharedErrorCodeEnum;
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

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl(['id' => 123]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItForbiddenForRegularUser(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl(['id' => 123]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItSuccessfullyUpdatesAttribute(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute = $this->getAttributeFixture()->create(code: 'brand');

        $payload = [
            'code' => 'brand_updated',
            'type' => 'string',
            'version' => $attribute->getVersion()->value(),
            'translations' => self::validTranslations(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $attribute->getId()->value()]),
            payload: $payload
        );

        $this->assertResponseIsSuccessful();

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Attribute was successfully updated', $data['message']);

        $updated = $this->getReadRepository()->findById($attribute->getId());
        $this->assertSame('brand_updated', $updated->getCode()->value());
    }

    public function testItReturns409OnConcurrencyError(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute = $this->getAttributeFixture()->create(code: 'old');

        $payload = [
            'code' => 'new',
            'type' => 'string',
            'translations' => self::validTranslations(),
            'version' => $attribute->getVersion()->value() + 1,
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $attribute->getId()->value()]),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $data = $this->getResponseData($client);
        $this->assertExceptionMessage(
            data: $data,
            expectedCode: SharedErrorCodeEnum::ConcurrencyError->value,
            expectedContainMessage: 'The Attribute has been already modified. Please refresh the page and try again'
        );
    }

    public function testItReturns409WhenCodeAlreadyExists(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $existingAttribute = $this->getAttributeFixture()->create(code: 'existing');
        $attribute = $this->getAttributeFixture()->create(code: 'old');

        $payload = [
            'code' => $existingAttribute->getCode()->value(),
            'type' => TypeEnum::String->value,
            'translations' => self::validTranslations(),
            'version' => $attribute->getVersion()->value(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $attribute->getId()->value()]),
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

    #[DataProvider('invalidAttributeProvider')]
    public function testItReturns422OnInvalidData(array $payload, array $expectedErrorFields): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute = $this->getAttributeFixture()->create();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $attribute->getId()->value()]),
            payload: [
                'version' => $attribute->getVersion()->value(),
                ...$payload,
            ],
        );

        $this->assertResponseIsUnprocessable();

        $data = $this->getResponseData($client);

        $this->assertValidationErrors(responseData: $data, expectedErrorFields: $expectedErrorFields);
    }

    public static function invalidAttributeProvider(): iterable
    {
        yield 'empty code' => [
            'payload' => [
                'code' => '',
                'type' => 'string',
                'translations' => self::validTranslations(),
            ],
            'expectedErrorFields' => ['code'],
        ];
        yield 'invalid type' => [
            'payload' => [
                'code' => 'brand',
                'type' => 'wrong_type',
                'translations' => self::validTranslations(),
            ],
            'expectedErrorFields' => ['type'],
        ];
        yield 'invalid locale and missed required locales' => [
            'payload' => [
                'code' => 'brand',
                'type' => 'string',
                'translations' => [
                    'xx' => ['name' => 'Name'],
                ],
            ],
            'expectedErrorFields' => ['translations', 'translations[xx]'],
        ];
        yield 'invalid version' => [
            'payload' => [
                'code' => 'brand',
                'type' => 'string',
                'version' => -1,
                'translations' => self::validTranslations(),
            ],
            'expectedErrorFields' => ['version'],
        ];
    }

    public function testItReturns404OnInvalidRouteParameterFormat(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: '/admin/api/v1/catalog/attributes/invalid-string'
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

    private static function validTranslations(): array
    {
        return [
            'en' => ['name' => 'Brand'],
            'uk' => ['name' => 'Бренд'],
        ];
    }
}
