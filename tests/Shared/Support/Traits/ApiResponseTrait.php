<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Traits;

use PHPUnit\Framework\Assert;
use RuntimeException;
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

    /**
     * @param array<string, mixed> $data
     */
    protected function assertExceptionMessage(array $data, string $expectedCode, string $expectedContainMessage): void
    {
        Assert::assertArrayHasKey('code', $data);
        Assert::assertArrayHasKey('message', $data);
        Assert::assertSame($expectedCode, $data['code']);
        Assert::assertStringContainsString($expectedContainMessage, $data['message']);
    }

    protected function assertValidationErrors(array $responseData, array $expectedErrorFields): void
    {
        $violations = $responseData['violations'] ?? throw new RuntimeException("No 'violations' key found in response");

        $actualErrorFields = array_column($violations, 'field');

        foreach ($expectedErrorFields as $field) {
            Assert::assertContains($field, $actualErrorFields, "Validation error for field '{$field}' not found");
        }
    }
}
