<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\AttributeOption;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionCodeException;
use App\Catalog\Domain\ValueObject\AttributeOption\Code;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CodeTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validCodeProvider')]
    public function testItCreatesValidCode(string $code, string $expected): void
    {
        $vo = Code::fromString($code);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validCodeProvider(): iterable
    {
        yield 'simple' => ['wifi7', 'wifi7'];
        yield 'trimmed' => ['  wifi7  ', 'wifi7'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Code::class,
            value: 'wifi7',
            anotherValue: 'different'
        );
    }

    #[DataProvider('invalidCodeProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidAttributeOptionCodeException::class);
        Code::fromString($invalidValue);
    }

    public static function invalidCodeProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'regex mismatch' => ['CODE'];
        yield 'starts with number' => ['1code'];
        yield 'contains special characters' => ['code!'];
        yield 'contains spaces' => ['code with spaces'];
        yield 'leading dash' => ['-code'];
        yield 'trailing dash' => ['code-'];
        yield 'multiple dashes' => ['some--code'];
        yield 'too long' => [str_repeat('a', Code::MAX_LENGTH + 1)];
    }
}
