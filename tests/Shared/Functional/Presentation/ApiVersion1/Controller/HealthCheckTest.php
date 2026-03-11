<?php

declare(strict_types=1);

namespace App\Tests\Shared\Functional\Presentation\ApiVersion1\Controller;

use App\Shared\Presentation\Http\ApiVersion1\Controller\HealthCheckController;
use App\Tests\Shared\Support\Traits\ApiRequestTrait;
use App\Tests\Shared\Support\Traits\ApiResponseTrait;
use App\Tests\Shared\Support\Traits\BaseUriTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class HealthCheckTest extends WebTestCase
{
    use ApiRequestTrait;
    use ApiResponseTrait;
    use BaseUriTrait;

    private const string ROUTE_NAME = HealthCheckController::ROUTE_NAME;
    private const string METHOD = Request::METHOD_GET;

    public function testIsSuccessful(): void
    {
        $client = static::createClient();

        $this->requestJson(
            client: $client,
            method: self::METHOD,
            uri: $this->getBaseUrl(self::ROUTE_NAME)
        );

        $this->assertResponseIsSuccessful();

        $data = $this->getResponseData($client);
        self::assertEquals(expected: 'OK', actual: $data['status']);
    }
}
