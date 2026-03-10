<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategorySlugException;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SlugTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validSlugProvider')]
    public function testItCreatesValidSlug(string $slug, string $expected): void
    {
        $vo = Slug::fromString($slug);

        $this->assertEquals($expected, $vo->value());
        $this->assertEquals($expected, (string) $vo);
    }

    public static function validSlugProvider(): iterable
    {
        yield 'simple' => ['valid-slug', 'valid-slug'];
        yield 'trimmed' => ['  valid-slug  ', 'valid-slug'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Slug::class,
            value: 'valid-slug',
            anotherValue: 'different'
        );
    }

    #[DataProvider('invalidSlugProvider')]
    public function testThrowsExceptionForInvalidSlug(string $invalidValue): void
    {
        $this->expectException(InvalidCategorySlugException::class);
        Slug::fromString($invalidValue);
    }

    public static function invalidSlugProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'regex mismatch' => ['S_L_U_G'];
        yield 'too long' => [str_repeat('a', Slug::MAX_LENGTH + 1)];
    }
}
