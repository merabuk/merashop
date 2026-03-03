<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ApiRequestTrait
{
    /**
     * @param array<string, mixed>  $parameters
     * @param array<string, mixed>  $payload
     * @param array<string, string> $server
     */
    protected function requestJson(
        KernelBrowser $client,
        string $method,
        string $uri,
        array $parameters = [],
        array $payload = [],
        array $server = [],
    ): void {
        $client->request(
            method: $method,
            uri: $uri,
            parameters: $parameters,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                ...$server,
            ],
            content: json_encode($payload)
        );
    }

    protected function withAuthorizationHeader(string $token): array
    {
        return [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ];
    }
}
