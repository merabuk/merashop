<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\ValueObject\OutboxEmail\Payload;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\TestCase;

final class PayloadTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidPayload(): void
    {
        $payload = ['key' => 'value'];
        $vo = Payload::fromArray($payload);

        self::assertSame($payload, $vo->value());
        self::assertSame(json_encode($payload), (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertArrayVOProvidesEqualityCheck(
            className: Payload::class,
            value: ['key1' => 'value1', 'key2' => 'value2'],
            shuffledValue: ['key2' => 'value2', 'key1' => 'value1'],
            anotherValue: ['key' => 'another-value'],
        );
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
