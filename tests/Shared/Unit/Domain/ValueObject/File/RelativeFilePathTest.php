<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\ValueObject\File;

use App\Shared\Domain\Exception\ValueObject\InvalidRelativePathException;
use App\Shared\Domain\ValueObject\File\RelativeFilePath;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class RelativeFilePathTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validRelativeFilePathProvider')]
    public function testItCreatesRelativeFilePath(string $path, string $expected): void
    {
        $vo = RelativeFilePath::fromString($path);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validRelativeFilePathProvider(): iterable
    {
        yield 'simple' => ['path/to/file.txt', 'path/to/file.txt'];
        yield 'trimmed' => ['   path/to/file.txt  ', 'path/to/file.txt'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: RelativeFilePath::class,
            value: 'path/to/file.txt',
            anotherValue: 'different/path.txt'
        );
    }

    #[DataProvider('invalidRelativeFilePathProvider')]
    public function testItThrowsExceptionForInvalidInput(?string $invalidValue): void
    {
        $this->expectException(InvalidRelativePathException::class);

        RelativeFilePath::fromString($invalidValue);
    }

    public static function invalidRelativeFilePathProvider(): iterable
    {
        yield 'null' => [null];
        yield 'empty' => [''];
        yield 'only spaces' => ['    '];
        yield 'with forbidden names' => ['../path/to/file.txt'];
        yield 'with spaces' => ['path to file.txt'];
        yield 'double slashes' => ['path//to/file.txt'];
        yield 'leading slash' => ['/path/to/file.txt'];
        yield 'trailing slash' => ['path/to/file.txt/'];
    }
}
