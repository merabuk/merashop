<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailBodyException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Body;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BodyTest extends TestCase
{
    public function testItCreatesValidBody(): void
    {
        $body = '<p>Hello, world!</p>';
        $vo = Body::fromString($body);

        self::assertSame($body, $vo->value());
        self::assertSame($body, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $body1 = '<p>Hello, world!</p>';
        $body2 = '<p>Hello, world.</p>';

        $vo1 = Body::fromString($body1);
        $vo2 = Body::fromString($body1);
        $vo3 = Body::fromString($body2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testItTrimsInput(): void
    {
        $body = '<p>Hello, world!</p>';
        $vo = Body::fromString('  '.$body.'  ');

        self::assertSame($body, $vo->value());
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
