<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeTypeException;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TypeTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('typeEnumProvider')]
    public function testItCreatesValidTypeFromEnum(TypeEnum $enum): void
    {
        $vo = Type::fromEnum($enum);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    #[DataProvider('typeEnumProvider')]
    public function testItCreatesValidTypeFromString(TypeEnum $enum): void
    {
        $vo = Type::fromString($enum->value);

        self::assertSame($enum, $vo->value());
        self::assertSame($enum->value, (string) $vo);
    }

    public static function typeEnumProvider(): iterable
    {
        foreach (TypeEnum::cases() as $case) {
            yield $case->name => [$case];
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
        yield 'string' => [Type::string(), TypeEnum::String, 'isString'];
        yield 'integer' => [Type::int(), TypeEnum::Int, 'isInt'];
        yield 'boolean' => [Type::boolean(), TypeEnum::Boolean, 'isBoolean'];
        yield 'select' => [Type::select(), TypeEnum::Select, 'isSelect'];
    }

    public function testItTrimsInput(): void
    {
        $type = TypeEnum::String;
        $vo = Type::fromString('  '.$type->value.'  ');
        self::assertTrue($vo->isString());
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEnumVOProvidesEqualityCheck(
            className: Type::class,
            enum: TypeEnum::String,
            anotherEnum: TypeEnum::Select
        );
    }

    #[DataProvider('invalidTypeProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidAttributeTypeException::class);
        Type::fromString($invalidValue);
    }

    public static function invalidTypeProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'only spaces' => ['   '];
        yield 'wrong case' => ['STRING'];
        yield 'random string' => ['not-a-type'];
    }
}
