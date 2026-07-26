<?php

declare(strict_types=1);

namespace App\Tests\Customer\Unit\Domain\Entity;

use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use App\Customer\Domain\ValueObject\CustomerProfile\PhoneNumber;
use App\Tests\Customer\Support\CustomerProfileMother;
use App\Tests\Shared\BaseUnitTest;

final class CustomerProfileTest extends BaseUnitTest
{
    public function testItUpdatesPersonalDataCorrectly(): void
    {
        $customerProfile = CustomerProfileMother::createWithData();

        self::assertFalse($customerProfile->isProfileCompleted());

        $newFirstName = FirstName::fromString('John');
        $newLastName = LastName::fromString('Doe');
        $newPhoneNumber = PhoneNumber::fromString('+380998877666');

        $customerProfile->updatePersonalData(
            firstName: $newFirstName,
            lastName: $newLastName,
            phoneNumber: $newPhoneNumber
        );

        self::assertTrue($customerProfile->getFirstName()->equals($newFirstName));
        self::assertTrue($customerProfile->getLastName()->equals($newLastName));
        self::assertTrue($customerProfile->getPhoneNumber()->equals($newPhoneNumber));

        self::assertTrue($customerProfile->isProfileCompleted());
    }
}
