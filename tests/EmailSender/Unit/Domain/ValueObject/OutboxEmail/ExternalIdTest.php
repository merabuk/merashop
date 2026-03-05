<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailSubjectException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\ExternalId;
use App\Tests\Shared\Unit\Domain\ValueObject\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ExternalIdTest extends TestCase
{
    use ValueObjectEqualityCheckTrait;

    #[DataProvider('validExternalIdProvider')]
    public function testItCreatesValidExternalId(string $externalId, string $expected): void
    {
        $vo = ExternalId::fromString($externalId);

        self::assertSame($expected, $vo->value());
        self::assertSame($expected, (string) $vo);
    }

    public static function validExternalIdProvider(): iterable
    {
        yield 'valid' => ['1234567890', '1234567890'];
        yield 'with spaces' => ['123 456 7890', '123 456 7890'];
        yield 'trimmed' => ['  1234567890  ', '1234567890'];
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertStringVOProvidesEqualityCheck(
            className: ExternalId::class,
            value: '1234567890',
            anotherValue: '0987654321'
        );
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
