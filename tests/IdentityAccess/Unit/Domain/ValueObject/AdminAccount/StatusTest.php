<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountStatusException;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Status;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StatusTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('statusEnumProvider')]
    public function testItCreatesValidStatusFromEnum(StatusEnum $enum): void
    {
        $vo = Status::fromEnum($enum);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    #[DataProvider('statusEnumProvider')]
    public function testItCreatesValidStatusFromString(StatusEnum $enum): void
    {
        $vo = Status::fromString($enum->value);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    public static function statusEnumProvider(): iterable
    {
        foreach (StatusEnum::cases() as $case) {
            yield $case->value => [$case];
        }
    }

    #[DataProvider('factoryMethodProvider')]
    public function testItCreatesCorrectStatusFromFactoryMethods(
        Status $vo,
        StatusEnum $expectedEnum,
        string $checkMethod,
    ): void {
        self::assertSame($expectedEnum, $vo->value());
        self::assertTrue($vo->$checkMethod());
    }

    public static function factoryMethodProvider(): iterable
    {
        yield 'active' => [Status::active(), StatusEnum::Active, 'isActive'];
        yield 'inactive' => [Status::inactive(), StatusEnum::Inactive, 'isInactive'];
        yield 'draft' => [Status::draft(), StatusEnum::Draft, 'isDraft'];
        yield 'blocked' => [Status::blocked(), StatusEnum::Blocked, 'isBlocked'];
        yield 'deleted' => [Status::deleted(), StatusEnum::Deleted, 'isDeleted'];
        yield 'vacation' => [Status::vacation(), StatusEnum::OnVacation, 'isOnVacation'];
    }

    public function testItTrimsInput(): void
    {
        $status = StatusEnum::Active;
        $vo = Status::fromString('  '.$status->value.'  ');
        self::assertTrue($vo->isActive());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEnumVOProvidesEqualityCheck(
            className: Status::class,
            enum: StatusEnum::Active,
            anotherEnum: StatusEnum::Blocked
        );
    }

    #[DataProvider('invalidStatusProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidAdminAccountStatusException::class);
        Status::fromString($invalidValue);
    }

    public static function invalidStatusProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only spaces' => ['   '];
        yield 'wrong case' => ['ACTIVE'];
        yield 'random string' => ['not-a-status'];
    }
}
