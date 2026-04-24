<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\Service\Utility;

use App\Shared\Domain\Service\Utility\StringHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StringHelperTest extends TestCase
{
    #[DataProvider('limitProvider')]
    public function testItLimitsStringsCorrectly(string $string, int $limit, ?string $ending, string $expected): void
    {
        self::assertSame($expected, StringHelper::limit(string: $string, limit: $limit, ending: $ending));
    }

    public static function limitProvider(): iterable
    {
        yield 'no limit needed' => ['Short', 10, '...', 'Short'];
        yield 'exact limit' => ['Hello', 5, '...', 'Hello'];
        yield 'limit with ending' => ['This is a long string', 4, '...', 'This...'];
        yield 'limit without ending' => ['Truncate this', 8, null, 'Truncate'];
        yield 'multibyte support' => ['Привіт Світ', 6, '...', 'Привіт...'];
        yield 'emoji support' => ['🚀🚀🚀', 1, '!', '🚀!'];
    }

    #[DataProvider('afterProvider')]
    public function testItGetsAfterCorrectly(string $string, string $search, string $expected): void
    {
        self::assertSame($expected, StringHelper::after(subject: $string, search: $search));
    }

    public static function afterProvider(): iterable
    {
        yield 'when search is empty' => ['Hello World', '', 'Hello World'];
        yield 'when search is not found' => ['Hello World', 'Foo', 'Hello World'];
        yield 'when search is found' => ['Hello World', 'Hello', ' World'];
    }
}
