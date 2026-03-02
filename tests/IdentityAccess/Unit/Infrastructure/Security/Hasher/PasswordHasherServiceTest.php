<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Hasher;

use App\IdentityAccess\Infrastructure\Security\Hasher\PasswordHasherService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

final class PasswordHasherServiceTest extends TestCase
{
    public function testItHashDelegatesToSymfonyHasher(): void
    {
        $symfonyHasher = $this->createMock(UserPasswordHasherInterface::class);
        $service = new PasswordHasherService($symfonyHasher);

        $plainPassword = 'plain-password';
        $hashedPassword = 'hashed-password';

        $symfonyHasher->expects(self::once())
            ->method('hashPassword')
            ->with(
                self::isInstanceOf(PasswordAuthenticatedUserInterface::class),
                $plainPassword
            )
            ->willReturn($hashedPassword);

        $result = $service->hash($plainPassword);
        self::assertSame($hashedPassword, $result);
    }

    public function testVerifyDelegatesToSymfonyHasher(): void
    {
        $symfonyHasher = $this->createMock(UserPasswordHasherInterface::class);
        $service = new PasswordHasherService($symfonyHasher);

        $plainPassword = 'plain-password';
        $hashedPassword = 'hashed-password';

        $symfonyHasher->expects(self::once())
            ->method('isPasswordValid')
            ->with(
                self::isInstanceOf(PasswordAuthenticatedUserInterface::class),
                $plainPassword
            )
            ->willReturn(true);

        $result = $service->verify($hashedPassword, $plainPassword);
        self::assertTrue($result);
    }
}
