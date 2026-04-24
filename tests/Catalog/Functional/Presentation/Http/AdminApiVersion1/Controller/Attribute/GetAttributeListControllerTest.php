<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Domain\Enum\Attribute\SortFieldEnum;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute\GetAttributeListController;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Tests\Catalog\Support\Traits\AttributeFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use App\Tests\Shared\Support\Traits\DbPerformanceTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeListControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use AttributeFactoryTrait;
    use BaseUriTrait;
    use DbPerformanceTrait;

    private const string ROUTE_NAME = GetAttributeListController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_GET;

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

    public function testItReturnsForbiddenForRegularUsers(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturnsPaginatedListForAdmin(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();
        $client->enableProfiler();

        $count = 5;
        $perPage = 2;
        $this->getAttributeFixture()->create(type: TypeEnum::String);
        $this->getAttributeFixture()->create(type: TypeEnum::Color);
        $this->getAttributeFixture()->create(type: TypeEnum::Select);
        $this->getAttributeFixture()->create(type: TypeEnum::MultiSelect);
        $this->getAttributeFixture()->create(type: TypeEnum::Integer);

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            parameters: ['perPage' => $perPage]
        );

        $this->assertSelectCountLessThanOrEqual(expectedMax: 3, client: $client, connectionName: 'catalog');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Range', sprintf('attributes %d/%d', $perPage, $count));

        $data = $this->getResponseData($client);
        $this->assertCount($perPage, $data);
        $this->assertResponseHeaderSame('X-Next-Cursor', (string) end($data)['ulid']);
    }

    public function testItFiltersBySearchTerm(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute = $this->getAttributeFixture()->create(code: 'color');
        $this->getAttributeFixture()->create(code: 'size');

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            parameters: [
                'filters' => ['search' => $attribute->getCode()->value()],
            ],
        );

        $data = $this->getResponseData($client);
        $this->assertCount(1, $data);
        $this->assertEquals($attribute->getCode()->value(), $data[0]['code']);
    }

    #[DataProvider('invalidPerPageProvider')]
    public function testItFailsOnInvalidPerPage(int $perPage): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            parameters: ['perPage' => $perPage]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function invalidPerPageProvider(): iterable
    {
        yield 'negative value' => [-1];
        yield 'zero value' => [0];
        yield 'above limit' => [101];
    }

    #[DataProvider('sortFieldProvider')]
    public function testSortFieldValidation(string $field, int $expectedStatus): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            parameters: ['sortField' => $field]
        );

        $this->assertResponseStatusCodeSame($expectedStatus);
    }

    public static function sortFieldProvider(): iterable
    {
        yield 'valid field' => [SortFieldEnum::Code->value, Response::HTTP_OK];
        yield 'invalid field' => ['invalid_field', Response::HTTP_UNPROCESSABLE_ENTITY];
    }

    #[DataProvider('sortDirectionProvider')]
    public function testSortDirectionValidation(string $direction, int $expectedStatus): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            parameters: [
                'sortDir' => $direction,
            ]
        );

        $this->assertResponseStatusCodeSame($expectedStatus);
    }

    public static function sortDirectionProvider(): iterable
    {
        yield 'invalid direction' => ['invalid_direction', Response::HTTP_UNPROCESSABLE_ENTITY];

        $validCases = ['asc', 'desc', Sort::ASC, Sort::DESC];
        foreach ($validCases as $validCase) {
            yield 'direction '.$validCase => [$validCase, Response::HTTP_OK];
        }
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}
