<?php

declare(strict_types=1);

namespace App\Tests\Shared\Unit\Domain\Service\Validation;

use App\Shared\Domain\Exception\Services\Validation\InvalidUrlFormatException;
use App\Shared\Domain\Service\Validation\UrlValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UrlValidatorTest extends TestCase
{
    #[DataProvider('validUrlProvider')]
    public function testItValidatesCorrectUrl(string $url, string $expected): void
    {
        $result = UrlValidator::normalize($url);

        self::assertSame($expected, $result);
    }

    public static function validUrlProvider(): iterable
    {
        yield 'valid' => ['https://example.com', 'https://example.com'];
        yield 'with trailing slash' => ['https://example.com/', 'https://example.com/'];
        yield 'trimmed' => ['  https://example.com  ', 'https://example.com'];
        yield 'with auth' => ['https://user:password@example.com', 'https://user:password@example.com'];
        yield 'with port' => ['https://example.com:8080', 'https://example.com:8080'];
        yield 'with path' => ['https://example.com/path', 'https://example.com/path'];
        yield 'with query' => ['https://example.com?query=value', 'https://example.com/?query=value'];
        yield 'with fragment' => ['https://example.com#fragment', 'https://example.com/#fragment'];
        yield 'idn domain' => ['https://мій.сайт', 'https://xn--i1af2f.xn--80aswg'];
    }

    #[DataProvider('invalidUrlProvider')]
    public function testThrowsExceptionWhenInvalidInput(
        string $invalidValue,
        array $allowedSchemes,
    ): void {
        $this->expectException(InvalidUrlFormatException::class);

        UrlValidator::normalize($invalidValue, $allowedSchemes);
    }

    public static function invalidUrlProvider(): iterable
    {
        yield 'empty' => [
            'invalidValue' => '',
            'allowedSchemes' => [],
        ];
        yield 'invalid' => [
            'invalidValue' => 'invalid-url',
            'allowedSchemes' => [],
        ];
        yield 'no domain' => [
            'invalidValue' => 'https://',
            'allowedSchemes' => [],
        ];
        yield 'no scheme' => [
            'invalidValue' => '//example.com',
            'allowedSchemes' => [],
        ];
        yield 'only http' => [
            'invalidValue' => 'ftp://example.com',
            'allowedSchemes' => UrlValidator::ONLY_HTTP,
        ];
    }
}
