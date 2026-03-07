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

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItForbiddenForRegularUser(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws InvalidAttributeCodeException
     */
    public function testItSuccessfullyCreatesAttribute(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $payload = [
            'code' => 'brand_name',
            'type' => TypeEnum::String->value,
            'translations' => self::validTranslations(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $this->getResponseData($client);
        self::assertArrayHasKey('message', $data);
        $this->assertStringContainsString('Attribute was successfully created', $data['message']);

        $exists = $this->getReadRepository()->existsByCode(Code::fromString($payload['code']));
        $this->assertTrue($exists, 'Attribute was not saved to database');
    }

    public function testItReturns409WhenCodeAlreadyExists(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $existingAttribute = $this->getAttributeFixture()->create();

        $payload = [
            'code' => $existingAttribute->getCode()->value(),
            'type' => TypeEnum::String->value,
            'translations' => self::validTranslations(),
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
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
