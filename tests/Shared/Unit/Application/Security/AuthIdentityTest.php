<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Application\Security;

use App\Shared\Application\Security\AuthIdentity;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Enum\RoleEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AuthIdentityTest extends TestCase
{
    /**
     * @param string[] $roles
     */
    #[DataProvider('authIdentityProvider')]
    public function testItCorrectlyIdentifiesEntity(
        IdentityTypeEnum $type,
        array $roles,
        bool $isUser,
        bool $isAdmin,
        bool $isModule,
        string $checkRole,
        bool $expectedHasRole,
    ): void {
        $identity = new AuthIdentity(id: '01ARZ3NDEKTSV4RRFFQ6KHNQZY', type: $type, roles: $roles);

        self::assertSame($isUser, $identity->isUser());
        self::assertSame($isAdmin, $identity->isAdmin());
        self::assertSame($isModule, $identity->isModule());
        self::assertSame($expectedHasRole, $identity->hasRole($checkRole));
    }

    public static function authIdentityProvider(): iterable
    {
        yield 'user identity' => [
            'type' => IdentityTypeEnum::User,
            'roles' => [RoleEnum::User->value],
            'isUser' => true,
            'isAdmin' => false,
            'isModule' => false,
            'checkRole' => RoleEnum::User->value,
            'expectedHasRole' => true,
        ];

        yield 'admin identity' => [
            'type' => IdentityTypeEnum::Admin,
            'roles' => [RoleEnum::Admin->value, RoleEnum::User->value],
            'isUser' => false,
            'isAdmin' => true,
            'isModule' => false,
            'checkRole' => RoleEnum::Admin->value,
            'expectedHasRole' => true,
        ];

        yield 'module identity' => [
            'type' => IdentityTypeEnum::Module,
            'roles' => ['ROLE_INTERNAL_MODULE'],
            'isUser' => false,
            'isAdmin' => false,
            'isModule' => true,
            'checkRole' => RoleEnum::Admin->value,
            'expectedHasRole' => false,
        ];
    }
}
