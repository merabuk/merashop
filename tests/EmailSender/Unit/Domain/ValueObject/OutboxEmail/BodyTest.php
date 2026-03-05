<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailBodyException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Body;
use App\Tests\Shared\Unit\Domain\ValueObject\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BodyTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validBodyProvider')]
    public function testItCreatesValidBody(string $body, string $expected): void
    {
        $vo = Body::fromString($body);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validBodyProvider(): iterable
    {
        yield 'valid' => ['<p>Hello, world!</p>', '<p>Hello, world!</p>'];
        yield 'with new lines' => ["\n<p>Hello, world!</p>\n", '<p>Hello, world!</p>'];
        yield 'trimmed' => ['   <p>Hello, world!</p>   ', '<p>Hello, world!</p>'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: Body::class,
            value: '<p>Hello, world!</p>',
            anotherValue: '<p>Hello, world.</p>'
        );
    }

    #[DataProvider('invalidBodyProvider')]
    public function testItThrowsExceptionOnInvalidInput(string $body): void
    {
        $this->expectException(InvalidOutboxEmailBodyException::class);
        Body::fromString($body);
    }

    public static function invalidBodyProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['    '];
        yield 'only new lines' => ["\n\n\n"];
    }

    public function testItSupportsComplexHtml(): void
    {
        $html = <<<'HTML'
        <div class="container">
            <h1>Welcome!</h1>
            <p>Follow <a href="https://example.com">this link</a>.</p>
        </div>
        HTML;

        $vo = Body::fromString($html);
        self::assertSame(mb_trim($html), $vo->value());
    }
}
