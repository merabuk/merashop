<?php

declare(strict_types=1);

namespace App\Tests\EmailSender\Unit\Domain\ValueObject\OutboxEmail;

use App\EmailSender\Domain\Exception\OutboxEmail\InvalidOutboxEmailToException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\To;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\EmailAddressTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ToTest extends TestCase
{
    use EmailAddressTestTrait;

    #[DataProvider('validEmailSampleProvider')]
    public function testItCreatesValidFrom(string $email, string $expected): void
    {
        $this->assertValidEmailAddress(
            className: To::class,
            email: $email,
            expected: $expected
        );
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEmailAddressEquality(To::class);
    }

    #[DataProvider('invalidEmailSampleProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidOutboxEmailToException::class);

        To::fromString($invalidValue);
    }

    protected static function getTotalLimit(): int
    {
        return To::MAX_LENGTH;
    }
}
