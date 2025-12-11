<?php

declare(strict_types=1);

namespace App\Users\Domain\Entity;

use App\Users\Domain\ValueObject\EmailAddress;
use App\Users\Domain\ValueObject\FirstName;
use App\Users\Domain\ValueObject\LastName;
use App\Users\Domain\ValueObject\PasswordHash;
use App\Users\Domain\ValueObject\PhoneNumber;
use App\Users\Domain\ValueObject\UserId;

class User
{
    public function __construct(
        private readonly ?UserId $id,
        private readonly EmailAddress $email,
        private readonly FirstName $firstName,
        private readonly LastName $lastName,
        private readonly ?PhoneNumber $phoneNumber,
        private readonly PasswordHash $password,
    ) {
    }

    public function getId(): ?UserId
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
