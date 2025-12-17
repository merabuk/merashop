<?php

declare(strict_types=1);

namespace App\Users\Domain\Entity;

use App\Users\Domain\ValueObject\EmailAddress;
use App\Users\Domain\ValueObject\FirstName;
use App\Users\Domain\ValueObject\Id;
use App\Users\Domain\ValueObject\LastName;
use App\Users\Domain\ValueObject\PasswordHash;
use App\Users\Domain\ValueObject\PhoneNumber;

readonly class User
{
    public function __construct(
        private ?Id $id,
        private EmailAddress $email,
        private FirstName $firstName,
        private LastName $lastName,
        private ?PhoneNumber $phoneNumber,
        private PasswordHash $password,
    ) {
    }

    public function getId(): ?Id
    {
        if (isset($this->id)) {
            return $this->id;
        }

        return null;
    }

    public function getEmail(): EmailAddress
    {
        return $this->email;
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

    public function getPassword(): PasswordHash
    {
        return $this->password;
    }
}
