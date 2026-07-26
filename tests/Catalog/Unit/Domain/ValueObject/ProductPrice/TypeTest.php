<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTypeException;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class TypeTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('typeEnumProvider')]
    public function testItCreatesValidStatusFromEnum(TypeEnum $enum): void
    {
        $vo = Type::fromEnum($enum);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    #[DataProvider('typeEnumProvider')]
    public function testItCreatesValidStatusFromString(TypeEnum $enum): void
    {
        $vo = Type::fromString($enum->value);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    public static function typeEnumProvider(): iterable
    {
        foreach (TypeEnum::cases() as $enum) {
            yield $enum->name => [$enum];
        }
    }

    #[DataProvider('factoryMethodProvider')]
    public function testItCreatesCorrectTypeFromFactoryMethods(
        Type $vo,
        TypeEnum $expectedEnum,
        string $checkMethod,
    ): void {
        self::assertSame($expectedEnum, $vo->value());
        self::assertTrue($vo->$checkMethod());
    }

    public static function factoryMethodProvider(): iterable
    {
        yield 'regular' => [Type::regular(), TypeEnum::Regular, 'isRegular'];
        yield 'sale' => [Type::sale(), TypeEnum::Sale, 'isSale'];
        yield 'cost' => [Type::cost(), TypeEnum::Cost, 'isCost'];
    }

    public function testItTrimsInput(): void
    {
        $type = TypeEnum::Cost;
        $vo = Type::fromString('  '.$type->value.'  ');
        self::assertTrue($vo->isCost());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEnumVOProvidesEqualityCheck(
            className: Type::class,
            enum: TypeEnum::Regular,
            anotherEnum: TypeEnum::Sale
        );
    }

    #[DataProvider('timeLimitedProvider')]
    public function testItIsTimeLimited(Type $vo, bool $expected): void
    {
        self::assertSame($expected, $vo->isTimeLimited());
    }

    public static function timeLimitedProvider(): iterable
    {
        yield 'regular' => [Type::regular(), false];
        yield 'sale' => [Type::sale(), true];
        yield 'cost' => [Type::cost(), false];
    }

    #[DataProvider('invalidTypeProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidProductPriceTypeException::class);
        Type::fromString($invalidValue);
    }

    public static function invalidTypeProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'wrong case' => ['REGULAR'];
        yield 'random string' => ['not-a-type'];
    }
}
