<?php

declare(strict_types=1);

namespace App\Users\Domain\Entity;

use App\Users\Domain\ValueObject\EmailAddress;

class User
{
    public function __construct(
        private readonly ?int $id,
        private readonly EmailAddress $email,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly string $phoneNumber,
        private readonly string $password
    ) {
    }

    public function getId(): ?int
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

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
