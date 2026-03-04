<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute\UpdateAttributeController;
use App\Shared\Domain\Enum\ErrorCodeEnum;
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

    public function testItSuccessfullyUpdatesAttribute(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute = $this->getAttributeFixture()->create(code: 'brand');

        $payload = [
            'code' => 'brand_updated',
            'type' => 'string',
            'version' => $attribute->getVersion()->value(),
            'translations' => ['en' => ['name' => 'Brand Updated']],
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(['id' => $attribute->getId()->value()]),
            payload: $payload
        );

        $this->assertResponseIsSuccessful();

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
            'translations' => ['en' => ['name' => 'Name']],
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
        $this->assertSame(ErrorCodeEnum::ConcurrencyError->value, $data['code']);
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
            'payload' => ['code' => '', 'type' => 'string', 'translations' => ['en' => ['name' => 'Name']]],
            'expectedErrorFields' => ['code'],
        ];
        yield 'invalid type' => [
            'payload' => ['code' => 'brand', 'type' => 'wrong_type', 'translations' => ['en' => ['name' => 'Name']]],
            'expectedErrorFields' => ['type'],
        ];
        yield 'invalid locale' => [
            'payload' => ['code' => 'brand', 'type' => 'string', 'translations' => ['xx' => ['name' => 'Name']]],
            'expectedErrorFields' => ['translations[xx]'],
        ];
        yield 'invalid version' => [
            'payload' => ['code' => 'brand', 'type' => 'string', 'version' => -1],
            'expectedErrorFields' => ['version'],
        ];
    }

    private function getReadRepository(): AttributeReadRepositoryInterface
    {
        return self::getContainer()->get(AttributeReadRepositoryInterface::class);
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}
