<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeCodeException;
use App\Catalog\Domain\Repository\AttributeReadRepositoryInterface;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute\CreateAttributeController;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use App\Tests\Shared\Support\Traits\DbPerformanceTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateAttributeControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;
    use DbPerformanceTrait;

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
            'type' => 'string',
            'translations' => [
                'en' => ['name' => 'Brand'],
                'uk' => ['name' => 'Бренд'],
            ],
        ];

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            payload: $payload
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $readRepository = self::getContainer()->get(AttributeReadRepositoryInterface::class);
        $exists = $readRepository->existsByCode(Code::fromString('brand_name'));
        $this->assertTrue($exists, 'Attribute was not saved to database');

        $data = $this->getResponseData($client);
        $this->assertStringContainsString('successfully created', $data['message']);
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
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}
