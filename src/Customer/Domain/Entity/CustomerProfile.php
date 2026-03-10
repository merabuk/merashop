<?php

declare(strict_types=1);

namespace App\Customer\Domain\Entity;

use App\Customer\Domain\ValueObject\CustomerProfile\FirstName;
use App\Customer\Domain\ValueObject\CustomerProfile\Id;
use App\Customer\Domain\ValueObject\CustomerProfile\LastName;
use App\Customer\Domain\ValueObject\CustomerProfile\PhoneNumber;
use App\Shared\Domain\ValueObject\Identity\Ulid;

class CustomerProfile
{
    public function __construct(
        private readonly Ulid $userUlid,
        private ?FirstName $firstName = null,
        private ?LastName $lastName = null,
        private ?PhoneNumber $phoneNumber = null,
        private readonly ?Id $id = null,
    ) {
    }

    public static function create(Ulid $userUlid): self
    {
        return new self(userUlid: $userUlid);
    }

    public function getUserUlid(): Ulid
    {
        return $this->userUlid;
    }

    public function getFirstName(): ?FirstName
    {
        return $this->firstName;
    }

    public function getLastName(): ?LastName
    {
        return $this->lastName;
    }

    public function getPhoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumber;
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function isProfileCompleted(): bool
    {
        return null !== $this->firstName && null !== $this->lastName && null !== $this->phoneNumber;
    }

    public function updatePersonalData(FirstName $firstName, LastName $lastName, ?PhoneNumber $phoneNumber): void
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phoneNumber = $phoneNumber;
    }
}
