<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Domain\ValueObject\UserAccount;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountEmailException;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\EmailAddressTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;

final class EmailAddressTest extends BaseUnitTest
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
        $this->expectException(InvalidUserAccountEmailException::class);

        EmailAddress::fromString($invalidValue);
    }

    protected static function getTotalLimit(): int
    {
        return EmailAddress::MAX_LENGTH;
    }
}
