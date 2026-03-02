<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Integration\Infrastructure\Security\Hasher;

use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PasswordHasherIntegrationTest extends KernelTestCase
{
    private PasswordHasherInterface $service;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->service = self::getContainer()->get(PasswordHasherInterface::class);
    }

    public function testFullPasswordLifecycle(): void
    {
        $password = 'secret-password-123';

        $hash = $this->service->hash($password);
        self::assertNotSame($password, $hash);

        self::assertTrue($this->service->verify($hash, $password));

        self::assertFalse($this->service->verify($hash, 'wrong-password'));
    }
}
