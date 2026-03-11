<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\RefreshToken;

use App\IdentityAccess\Domain\Exception\RefreshToken\InvalidRefreshTokenAccountTypeException;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountType;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AccountTypeTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('accountTypeEnumProvider')]
    public function testItCreatesValidAccountTypeFromEnum(IdentityTypeEnum $accountType): void
    {
        $vo = AccountType::fromEnum($accountType);

        self::assertSame($accountType, $vo->value());
        self::assertSame($accountType->value, (string) $vo);
    }

    #[DataProvider('accountTypeEnumProvider')]
    public function testItCreatesValidAccountTypeFromString(IdentityTypeEnum $accountType): void
    {
        $vo = AccountType::fromString($accountType->value);

        self::assertSame($accountType, $vo->value());
        self::assertSame($accountType->value, (string) $vo);
    }

    public static function accountTypeEnumProvider(): iterable
    {
        foreach (IdentityTypeEnum::cases() as $case) {
            yield $case->value => [$case];
        }
    }

    #[DataProvider('factoryMethodProvider')]
    public function testItCreatesCorrectAccountTypeFromFactoryMethods(
        AccountType $vo,
        IdentityTypeEnum $expectedEnum,
        string $checkMethod,
    ): void {
        self::assertSame($expectedEnum, $vo->value());
        self::assertTrue($vo->$checkMethod());
    }

    public static function factoryMethodProvider(): iterable
    {
        yield 'admin' => [AccountType::admin(), IdentityTypeEnum::Admin, 'isAdmin'];
        yield 'user' => [AccountType::user(), IdentityTypeEnum::User, 'isUser'];
        yield 'module' => [AccountType::module(), IdentityTypeEnum::Module, 'isModule'];
    }

    public function testItTrimsInput(): void
    {
        $accountType = IdentityTypeEnum::Admin;
        $vo = AccountType::fromString('  '.$accountType->value.'  ');
        self::assertTrue($vo->isAdmin());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEnumVOProvidesEqualityCheck(
            className: AccountType::class,
            enum: IdentityTypeEnum::Admin,
            anotherEnum: IdentityTypeEnum::User
        );
    }

    #[DataProvider('invalidAccountTypeProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidRefreshTokenAccountTypeException::class);
        AccountType::fromString($invalidValue);
    }

    public static function invalidAccountTypeProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['    '];
        yield 'wrong case' => ['ADMIN'];
        yield 'random string' => ['not-an-account-type'];
    }
}
