<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeTypeException;
use App\Catalog\Domain\ValueObject\Attribute\Type;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class TypeTest extends BaseUnitTest
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

    public function testItTrimsInputAndValidatesCase(): void
    {
        $vo = Type::fromString('  string  ');
        self::assertTrue($vo->is(TypeEnum::String));
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
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'wrong case' => ['STRING'];
        yield 'unknown' => ['unknown_type'];
    }

    #[DataProvider('booleanMethodsProvider')]
    public function testBooleanMethods(TypeEnum $enum, bool $hasOptions, bool $hasMetadata): void
    {
        $vo = Type::fromEnum($enum);

        self::assertSame($hasOptions, $vo->hasOptions(), "Failed hasOptions for {$enum->name}");
        self::assertSame($hasMetadata, $vo->hasOptionMetadata(), "Failed hasMetadata for {$enum->name}");
    }

    public static function booleanMethodsProvider(): iterable
    {
        yield 'string' => [TypeEnum::String, false, false];
        yield 'text' => [TypeEnum::Text, false, false];
        yield 'integer' => [TypeEnum::Integer, false, false];
        yield 'float' => [TypeEnum::Float, false, false];
        yield 'boolean' => [TypeEnum::Boolean, false, false];
        yield 'select' => [TypeEnum::Select, true, false];
        yield 'multiselect' => [TypeEnum::MultiSelect, true, false];
        yield 'color' => [TypeEnum::Color, false, false];
        yield 'date' => [TypeEnum::Date, false, false];
        yield 'url' => [TypeEnum::Url, false, false];
        yield 'dimension' => [TypeEnum::Dimension, true, true];
        yield 'image' => [TypeEnum::Image, false, false];
    }

    #[DataProvider('allowChangeProvider')]
    public function testItAllowChange(TypeEnum $current, TypeEnum $new, bool $expected): void
    {
        self::assertSame($expected, Type::fromEnum($current)->allowChange(Type::fromEnum($new)));
    }

    public static function allowChangeProvider(): iterable
    {
        yield 'identity' => [TypeEnum::String, TypeEnum::String, true];
        yield 'string to text' => [TypeEnum::String, TypeEnum::Text, true];
        yield 'text to string' => [TypeEnum::Text, TypeEnum::String, true];
        yield 'string to select' => [TypeEnum::String, TypeEnum::Select, false];
        yield 'integer to float' => [TypeEnum::Integer, TypeEnum::Float, false];
    }
}
