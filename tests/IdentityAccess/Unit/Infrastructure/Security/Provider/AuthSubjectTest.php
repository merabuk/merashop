<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Provider;

use App\IdentityAccess\Infrastructure\Security\Provider\AuthSubject;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Enum\RoleEnum;
use App\Tests\IdentityAccess\Support\AdminAccountMother;
use App\Tests\IdentityAccess\Support\ModuleAccountMother;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use PHPUnit\Framework\TestCase;

final class AuthSubjectTest extends TestCase
{
    public function testFromUserAccount(): void
    {
        $user = UserAccountMother::createWithData();
        $subject = AuthSubject::fromUserAccount($user);

        self::assertSame(IdentityTypeEnum::User, $subject->getType());
        self::assertSame($user->getUlid()->value(), $subject->getUlid());
        self::assertSame($user->getEmail()->value(), $subject->getUserIdentifier());
        self::assertSame($user->getRoles()->toStrings(), $subject->getRoles());
    }

    public function testFromModuleAccountTransformsScopesToRoles(): void
    {
        $module = ModuleAccountMother::createWithData(scopes: ['user:read', 'product.write']);
        $subject = AuthSubject::fromModuleAccount($module);

        self::assertSame(IdentityTypeEnum::Module, $subject->getType());
        self::assertSame($module->getUlid()->value(), $subject->getUlid());
        self::assertSame($module->getClientId()->value(), $subject->getUserIdentifier());

        $expectedRoles = [
            ...$module->getScopes()->toStrings(),
            RoleEnum::Module->value,
        ];

        self::assertEqualsCanonicalizing($expectedRoles, $subject->getRoles());
    }

    public function testFromAdminAccount(): void
    {
        $admin = AdminAccountMother::createWithData();
        $subject = AuthSubject::fromAdminAccount($admin);

        self::assertSame(IdentityTypeEnum::Admin, $subject->getType());
        self::assertSame($admin->getUlid()->value(), $subject->getUlid());
        self::assertSame($admin->getEmail()->value(), $subject->getUserIdentifier());
        self::assertSame($admin->getRoles()->toStrings(), $subject->getRoles());
    }
}
