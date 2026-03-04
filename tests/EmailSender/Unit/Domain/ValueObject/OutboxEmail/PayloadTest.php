<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\ValueObject\OutboxEmail\Payload;
use PHPUnit\Framework\TestCase;

final class PayloadTest extends TestCase
{
    public function testItCreatesValidPayload(): void
    {
        $payload = ['key' => 'value'];
        $vo = Payload::fromArray($payload);

        self::assertSame($payload, $vo->value());
        self::assertSame(json_encode($payload), (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $payload1 = ['key1' => 'value1'];
        $payload2 = ['key2' => 'value2'];

        $vo1 = Payload::fromArray($payload1);
        $vo2 = Payload::fromArray($payload1);
        $vo3 = Payload::fromArray($payload2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function tetsItCreatesEmptyPayload(): void
    {
        $vo = Payload::empty();

        self::assertSame([], $vo->value());
    }

    public function testItGetsFromPayload(): void
    {
        $payload = ['key' => 'value'];
        $vo = Payload::fromArray($payload);

        self::assertSame($payload['key'], $vo->get('key'));
        self::assertNull($vo->get('non-existent-key'));
        self::assertSame('default', $vo->get('non-existent-key', 'default'));
    }
}
