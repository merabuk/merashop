<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class HealthCheckTest extends WebTestCase
{
    public function testIsSuccessful(): void
    {
        $client = static::createClient();

        $client->request(method: Request::METHOD_GET, uri: '/health-check');

        $this->assertResponseIsSuccessful();

        $jsonResult = json_decode(json: $client->getResponse()->getContent(), associative: true);
        $this->assertEquals(expected: 'OK', actual: $jsonResult['status']);
    }
}
