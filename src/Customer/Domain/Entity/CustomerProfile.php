<?php

declare(strict_types=1);

namespace App\Customer\Domain\Entity;

use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use App\Customer\Domain\ValueObject\CustomerProfile\PhoneNumber;
use App\Shared\Domain\ValueObject\Ulid;

class CustomerProfile
{
    public function __construct(
        private readonly Ulid $userUlid,
        private FirstName $firstName,
        private LastName $lastName,
        private ?PhoneNumber $phoneNumber = null,
    ) {
    }

    public function getUserUlid(): Ulid
    {
        return $this->userUlid;
    }

    public function getFirstName(): FirstName
    {
        return $this->firstName;
    }

    public function getLastName(): LastName
    {
        return $this->lastName;
    }

    public function getPhoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function updatePersonalData(FirstName $firstName, LastName $lastName, ?PhoneNumber $phoneNumber): void
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phoneNumber = $phoneNumber;
    }
}
