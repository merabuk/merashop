<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Product;

use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\Exception\Product\InvalidProductStatusException;
use App\Catalog\Domain\ValueObject\Product\Status;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class StatusTest extends BaseUnitTest
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
        foreach (StatusEnum::cases() as $enum) {
            yield $enum->name => [$enum];
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
        yield 'draft' => [Status::draft(), StatusEnum::Draft, 'isDraft'];
        yield 'archived' => [Status::archived(), StatusEnum::Archived, 'isArchived'];
    }

    public function testItTrimsInput(): void
    {
        $status = StatusEnum::Archived;
        $vo = Status::fromString('  '.$status->value.'  ');
        self::assertTrue($vo->isArchived());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEnumVOProvidesEqualityCheck(
            className: Status::class,
            enum: StatusEnum::Active,
            anotherEnum: StatusEnum::Draft
        );
    }

    #[DataProvider('invalidStatusProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidProductStatusException::class);
        Status::fromString($invalidValue);
    }

    public static function invalidStatusProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'wrong case' => ['ACTIVE'];
        yield 'random string' => ['not-a-status'];
    }
}
