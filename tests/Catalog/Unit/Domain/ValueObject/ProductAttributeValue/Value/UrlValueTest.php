<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeUrlValueException;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\UrlValue;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UrlValueTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validUrlProvider')]
    public function testItCreatesValidUrlValue(string $value, string $expected): void
    {
        $vo = UrlValue::fromString($value);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validUrlProvider(): iterable
    {
        yield 'simple' => ['https://example.com', 'https://example.com'];
        yield 'trimmed' => ['  https://example.com ', 'https://example.com'];
        yield 'with path' => ['https://example.com/path', 'https://example.com/path'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: UrlValue::class,
            value: 'https://example.com/path',
            anotherValue: 'https://example.com/different/path'
        );
    }

    #[DataProvider('invalidUrlProvider')]
    public function testThrowsExceptionsWithInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidProductAttributeUrlValueException::class);

        UrlValue::fromString($invalidValue);
    }

    public static function invalidUrlProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'invalid' => ['invalid-url'];
        yield 'ftp scheme' => ['ftp://example.com'];
        yield 'file scheme' => ['file://example.com'];
    }
}
