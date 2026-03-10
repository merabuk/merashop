<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    /**
     * @param array<string, UploadedFile> $files
     * @param array<string, mixed>        $parameters
     */
    protected function requestMultipart(
        KernelBrowser $client,
        string $method,
        string $uri,
        array $files = [],
        array $parameters = [],
        array $server = [],
    ): void {
        $client->request(
            method: $method,
            uri: $uri,
            parameters: $parameters,
            files: $files,
            server: [
                'HTTP_ACCEPT' => 'application/json',
                ...$server,
            ]
        );
    }

    protected function withAuthorizationHeader(string $token): array
    {
        return [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        ];
    }
}
