<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Infrastructure\Presentation\ApiVersion1\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class HealthCheckTest extends WebTestCase
{
    public function testIsSuccessful(): void
    {
        $client = static::createClient();

        $client->request(method: Request::METHOD_GET, uri: '/api/v1/health-check');

        $this->assertResponseIsSuccessful();

        $jsonResult = json_decode(json: $client->getResponse()->getContent(), associative: true);
        self::assertEquals(expected: 'OK', actual: $jsonResult['status']);
    }
}
