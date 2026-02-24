<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\Entity;

use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordHash;
use App\Tests\IdentityAccess\Support\AdminAccountMother;
use PHPUnit\Framework\TestCase;

class AdminAccountTest extends TestCase
{
    public function testItChangesPasswordCorrectly(): void
    {
        $admin = AdminAccountMother::createWithData();

        self::assertNull($admin->getPasswordChangedAt());
        self::assertTrue($admin->isPasswordChangeRequired());

        $admin->changePassword(PasswordHash::fromString('$2y$13$pciFCYAf/sJhshT0jNvZcuKRuwqJ8bSwCtbPN9g4dJoDmeU0Bn.d6'));

        self::assertFalse($admin->isPasswordChangeRequired());
        self::assertNotNull($admin->getPasswordChangedAt());
    }
}
