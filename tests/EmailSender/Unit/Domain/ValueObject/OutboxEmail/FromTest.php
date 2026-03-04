<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailFromException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\From;
use App\Tests\Shared\Unit\Domain\ValueObject\EmailAddressTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FromTest extends TestCase
{
    use EmailAddressTestTrait;

    public function testItCreatesValidFrom(): void
    {
        $this->assertValidEmailAddress(From::class);
    }

    public function testItTrimsSpaces(): void
    {
        $this->assertEmailAddressTrimming(From::class);
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEmailAddressEquality(From::class);
    }

    #[DataProvider('invalidEmailSampleProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidOutboxEmailFromException::class);

        From::fromString($invalidValue);
    }

    protected static function getTotalLimit(): int
    {
        return From::MAX_LENGTH;
    }
}
