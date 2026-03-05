<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\AdminAccount;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountEmailException;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\Tests\Shared\Unit\Domain\ValueObject\EmailAddressTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailAddressTest extends TestCase
{
    use EmailAddressTestTrait;

    #[DataProvider('validEmailSampleProvider')]
    public function testItCreatesValidEmailAddress(string $email, string $expected): void
    {
        $this->assertValidEmailAddress(
            className: EmailAddress::class,
            email: $email,
            expected: $expected
        );
    }

    public function testItProvidesEqualityCheck(): void
    {
        $this->assertEmailAddressEquality(EmailAddress::class);
    }

    #[DataProvider('invalidEmailSampleProvider')]
    public function testThrowsExceptionOnInvalidInput(string $invalidValue): void
    {
        $this->expectException(InvalidAdminAccountEmailException::class);

        EmailAddress::fromString($invalidValue);
    }

    protected static function getTotalLimit(): int
    {
        return EmailAddress::MAX_LENGTH;
    }
}
