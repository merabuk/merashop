<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute\GetAttributeListController;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Tests\Catalog\Support\Traits\AttributeFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use App\Tests\Shared\Support\Traits\DbPerformanceTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeListControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use AttributeFactoryTrait;
    use BaseUriTrait;
    use DbPerformanceTrait;

    private const string ROUTE_NAME = GetAttributeListController::ROUTE_NAME;

    public function testItReturnsForbiddenForGuests(): void
    {
        $client = self::createClient();
        $this->clearIdentity();

        $client->request(method: Request::METHOD_GET, uri: $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturnsForbiddenForRegularUsers(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $client->request(method: Request::METHOD_GET, uri: $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturnsPaginatedListForAdmin(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();
        $client->enableProfiler();

        $count = 5;
        $perPage = 2;
        $this->getAttributeFixture()->createMany($count);

        $client->request(method: Request::METHOD_GET, uri: $this->getUrl(), parameters: ['perPage' => $perPage]);

        $this->assertSelectCountLessThanOrEqual(expectedMax: 3, client: $client, connectionName: 'catalog');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Range', sprintf('attributes %d/%d', $perPage, $count));

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount($perPage, $data);
        $this->assertResponseHeaderSame('X-Next-Cursor', (string) end($data)['id']);
    }

    public function testItFiltersBySearchTerm(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $attribute = $this->getAttributeFixture()->create([
            'code' => 'color',
        ]);
        $this->getAttributeFixture()->create([
            'code' => 'size',
        ]);

        $client->request(method: Request::METHOD_GET, uri: $this->getUrl(), parameters: [
            'filter' => ['search' => $attribute->getCode()->value()],
        ]);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $data);
        $this->assertEquals($attribute->getCode()->value(), $data[0]['code']);
    }

    public function testItFailsOnInvalidPerPage(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $invalidCases = [-1, 0, 101];

        foreach ($invalidCases as $invalidCase) {
            $client->request(method: Request::METHOD_GET, uri: $this->getUrl(), parameters: ['perPage' => $invalidCase]);
            $this->assertResponseIsUnprocessable();
        }
    }

    public function testItFailsOnInvalidSortField(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $invalidCases = ['invalid_field'];

        foreach ($invalidCases as $invalidCase) {
            $client->request(method: Request::METHOD_GET, uri: $this->getUrl(), parameters: ['sortField' => $invalidCase]);
            $this->assertResponseIsUnprocessable();
        }

        $validCases = ['code'];

        foreach ($validCases as $validCase) {
            $client->request(method: Request::METHOD_GET, uri: $this->getUrl(), parameters: ['sortField' => $validCase]);
            $this->assertResponseIsSuccessful();
        }
    }

    public function testInFailsOnInvalidSortDirection(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $invalidCases = ['string'];

        foreach ($invalidCases as $invalidCase) {
            $client->request(method: Request::METHOD_GET, uri: $this->getUrl(), parameters: ['sortDir' => $invalidCase]);
            $this->assertResponseIsUnprocessable();
        }

        $validCases = ['asc', 'desc', Sort::ASC, Sort::DESC];

        foreach ($validCases as $validCase) {
            $client->request(method: Request::METHOD_GET, uri: $this->getUrl(), parameters: ['sortDir' => $validCase]);
            $this->assertResponseIsSuccessful();
        }
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}
