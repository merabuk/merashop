<?php

namespace App\Users\Domain\ValueObject;

use App\Users\Domain\Exception\InvalidEmailAddressException;

final class EmailAddress
{
    private string $email;

    /**
     * @throws InvalidEmailAddressException
     */
    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailAddressException::becauseItIsNotValidEmailAddress($email);
        }

        $this->email = mb_strtolower($email);
    }

    public function toString(): string
    {
        return $this->email;
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    public function equals(self $other): bool
    {
        return $this->email === $other->email;
    }
}
