<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Functional\Presentation\Http\AdminApiVersion1\Controller\Attribute;

use App\Catalog\Presentation\Http\AdminApiVersion1\Controller\Attribute\GetAttributeListController;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Tests\Catalog\Support\Traits\AttributeFactoryTrait;
use App\Tests\Shared\Support\Traits\ApiAuthTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetAttributeListControllerTest extends WebTestCase
{
    use ApiAuthTrait;
    use AttributeFactoryTrait;
    use BaseUriTrait;

    private const string ROUTE_NAME = GetAttributeListController::ROUTE_NAME;

    public function testItReturnsForbiddenForGuests(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturnsForbiddenForRegularUsers(): void
    {
        $client = self::createClient();
        $this->loginAsUser();

        $client->request(Request::METHOD_GET, $this->getUrl());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testItReturnsPaginatedListForAdmin(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $count = 5;
        $perPage = 2;
        $this->getAttributeFixture()->createMany($count);

        $client->request(Request::METHOD_GET, $this->getUrl(), ['perPage' => $perPage]);

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

        $client->request(Request::METHOD_GET, $this->getUrl(), [
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
            $client->request(Request::METHOD_GET, $this->getUrl(), ['perPage' => $invalidCase]);
            $this->assertResponseIsUnprocessable();
        }
    }

    public function testItFailsOnInvalidSortField(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $invalidCases = ['invalid_field'];

        foreach ($invalidCases as $invalidCase) {
            $client->request(Request::METHOD_GET, $this->getUrl(), ['sortField' => $invalidCase]);
            $this->assertResponseIsUnprocessable();
        }

        $validCases = ['code'];

        foreach ($validCases as $validCase) {
            $client->request(Request::METHOD_GET, $this->getUrl(), ['sortField' => $validCase]);
            $this->assertResponseIsSuccessful();
        }
    }

    public function testInFailsOnInvalidSortDirection(): void
    {
        $client = self::createClient();
        $this->loginAsAdmin();

        $invalidCases = ['string'];

        foreach ($invalidCases as $invalidCase) {
            $client->request(Request::METHOD_GET, $this->getUrl(), ['sortDir' => $invalidCase]);
            $this->assertResponseIsUnprocessable();
        }

        $validCases = ['asc', 'desc', Sort::ASC, Sort::DESC];

        foreach ($validCases as $validCase) {
            $client->request(Request::METHOD_GET, $this->getUrl(), ['sortDir' => $validCase]);
            $this->assertResponseIsSuccessful();
        }
    }

    private function getUrl(array $params = []): string
    {
        return $this->getBaseUrl(self::ROUTE_NAME, $params);
    }
}
