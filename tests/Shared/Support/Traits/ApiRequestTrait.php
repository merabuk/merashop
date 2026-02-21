<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ApiRequestTrait
{
    protected function requestJson(
        KernelBrowser $client,
        string $method,
        string $uri,
        array $payload = [],
    ): void {
        $client->request(
            method: $method,
            uri: $uri,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($payload)
        );
    }
}
