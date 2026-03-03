<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailSubjectException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\ExternalId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ExternalIdTest extends TestCase
{
    public function testItCreatesValidExternalId(): void
    {
        $externalId = '1234567890';
        $vo = ExternalId::fromString($externalId);

        self::assertSame($externalId, $vo->value());
        self::assertSame($externalId, (string) $vo);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $externalId1 = '1234567890';
        $externalId2 = '0987654321';

        $vo1 = ExternalId::fromString($externalId1);
        $vo2 = ExternalId::fromString($externalId1);
        $vo3 = ExternalId::fromString($externalId2);

        self::assertTrue($vo1->equals($vo2));
        self::assertFalse($vo1->equals($vo3));
    }

    public function testItTrimsInput(): void
    {
        $externalId = '1234567890';
        $vo = ExternalId::fromString('  '.$externalId.'  ');

        self::assertSame($externalId, $vo->value());
    }

    #[DataProvider('invalidExternalIdProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidOutboxEmailSubjectException::class);
        ExternalId::fromString($invalidValue);
    }

    public static function invalidExternalIdProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'string with only spaces' => ['   '];
        yield 'too long' => [str_repeat('a', ExternalId::MAX_LENGTH + 1)];
    }
}
