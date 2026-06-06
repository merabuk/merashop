<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Category;

use App\Catalog\Domain\Enum\Category\SortFieldEnum;
use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Category\GetCategoryListController;
use App\Tests\Catalog\Support\Traits\CategoryFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use App\Tests\Shared\Support\Traits\CriteriaPagingTrait;
use App\Tests\Shared\Support\Traits\DbPerformanceTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetCategoryListControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use ApiRequestTrait;
    use ApiResponseTrait;
    use CategoryFactoryTrait;
    use BaseUriTrait;
    use DbPerformanceTrait;
    use CriteriaPagingTrait;

    private const string ROUTE_NAME = GetCategoryListController::ROUTE_NAME;
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

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturnsForbiddenForRegularUsers(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $this->requestJson(client: $client, method: self::METHOD, uri: $this->getUrl());

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturnsPaginatedListForAdmin(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();
        $this->enableProfiler(client: $client);

        $count = 5;
        $perPage = 2;

        $this->getCategoryFixture()->createMany(count: $count);

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            parameters: [self::PER_PAGE_FIELD => $perPage]
        );

        $this->assertSelectCountLessThanOrEqual(expectedMax: 3, client: $client, connectionName: 'catalog');

        self::assertResponseIsSuccessful();
        $this->assertResponseHasContentRange(unit: 'categories', perPage: $perPage, total: $count);

        $data = $this->getResponseData($client);
        self::assertCount($perPage, $data);
        self::assertResponseHeaderSame('X-Next-Cursor', (string) end($data)['ulid']);
    }

    public function testItFiltersBySearchTerm(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $category = $this->getCategoryFixture()->create(slug: 'electronics');
        $this->getCategoryFixture()->create(slug: 'gadgets');

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getUrl(),
            parameters: [
                self::FILTERS_FIELD => ['search' => $category->getSlug()->value()],
            ],
        );

        self::assertResponseIsSuccessful();

        $data = $this->getResponseData($client);
        self::assertCount(1, $data);
        self::assertSame($category->getSlug()->value(), $data[0]['slug']);
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
            parameters: [self::PER_PAGE_FIELD => $perPage]
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
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
            parameters: [self::SORT_FIELD => $field]
        );

        self::assertResponseStatusCodeSame($expectedStatus);
    }

    public static function sortFieldProvider(): iterable
    {
        yield 'valid field' => [SortFieldEnum::Slug->value, Response::HTTP_OK];
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
                self::SORT_DIRECTION_FIELD => $direction,
            ]
        );

        self::assertResponseStatusCodeSame($expectedStatus);
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}
