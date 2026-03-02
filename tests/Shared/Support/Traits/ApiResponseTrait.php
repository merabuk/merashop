<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ApiResponseTrait
{
    protected function getResponseStatusCode(KernelBrowser $client): int
    {
        return $client->getResponse()->getStatusCode();
    }

    protected function getResponseData(KernelBrowser $client): array
    {
        return json_decode($client->getResponse()->getContent(), true) ?: [];
    }
}
