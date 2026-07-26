<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryPathException;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class PathTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validPathProvider')]
    public function testItCreatesValidPath(string $path, string $expected): void
    {
        $vo = Path::fromString($path);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validPathProvider(): iterable
    {
        yield 'simple' => ['/parent-category-sug/category-slug', '/parent-category-sug/category-slug'];
        yield 'trimmed' => ['  /category-slug  ', '/category-slug'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Path::class,
            value: '/parent-category-sug/category-slug',
            anotherValue: '/another-category-slug'
        );
    }

    public function testItCreatesRootPathCorrectly(): void
    {
        $slug = Slug::fromString('root');
        $vo = Path::root($slug);

        self::assertSame('/root', $vo->value());
    }

    #[DataProvider('validPathGenerateProvider')]
    public function testItGeneratesPathCorrectly(Slug $slug, ?Path $parentPath, string $expected): void
    {
        $vo = Path::generate($slug, $parentPath);

        self::assertSame($expected, $vo->value());
    }

    public static function validPathGenerateProvider(): iterable
    {
        yield 'root' => [Slug::fromString('root'), null, '/root'];
        yield 'child' => [Slug::fromString('child'), Path::fromString('/parent'), '/parent/child'];
    }

    #[DataProvider('invalidPathProvider')]
    public function testThrowsExceptionForInvalidPath(string $invalidValue): void
    {
        $this->expectException(InvalidCategoryPathException::class);
        Path::fromString($invalidValue);
    }

    public static function invalidPathProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
        yield 'double slash' => ['//invalid-path'];
        yield 'has inner spaces' => ['/invalid path'];
        yield 'does not start with separator' => ['invalid-path'];
        yield 'too long' => [str_repeat('a', Path::MAX_LENGTH + 1)];
    }

    #[DataProvider('hierarchyProvider')]
    public function testItCorrectlyIdentifiesDescendants(string $path, string $parentPath, bool $expected): void
    {
        $vo = Path::fromString($path);
        $parentVo = Path::fromString($parentPath);

        self::assertSame($expected, $vo->startsWith($parentVo));
    }

    public static function hierarchyProvider(): iterable
    {
        yield 'is direct child' => ['/electronics/tvs', '/electronics', true];
        yield 'is nested descendant' => ['/electronics/tvs/smart-tvs', '/electronics', true];
        yield 'is same path' => ['/electronics', '/electronics', false];
        yield 'is different branch' => ['/home/furniture', '/electronics', false];
        yield 'is similar prefix but not child' => ['/category-long', '/category', false];
    }
}
