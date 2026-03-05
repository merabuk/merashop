<?php

declare(strict_types=1);

namespace App\Customer\Domain\Factory;

use App\Customer\Domain\Entity\CustomerProfile;
use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileFirstNameException;
use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileLastNameException;
use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfilePhoneNumberException;
use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileUlidException;
use App\Customer\Domain\Factory\Contract\CustomerProfileFactoryInterface;
use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use App\Customer\Domain\ValueObject\CustomerProfile\PhoneNumber;
use App\Customer\Domain\ValueObject\CustomerProfile\Ulid;

class CustomerProfileFactory implements CustomerProfileFactoryInterface
{
    /**
     * @throws InvalidCustomerProfileFirstNameException
     * @throws InvalidCustomerProfileLastNameException
     * @throws InvalidCustomerProfilePhoneNumberException
     * @throws InvalidCustomerProfileUlidException
     */
    public function createForTest(
        string $userUlid,
        string $firstName,
        string $lastName,
        string $phoneNumber,
    ): CustomerProfile {
        return new CustomerProfile(
            userUlid: Ulid::fromString($userUlid),
            firstName: FirstName::fromString($firstName),
            lastName: LastName::fromString($lastName),
            phoneNumber: PhoneNumber::fromString($phoneNumber),
        );
    }
}
