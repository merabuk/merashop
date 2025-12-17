<?php

namespace App\Users\Domain\ValueObject;

use App\Shared\Domain\Exception\InvalidPhoneNumberException;
use App\Shared\Domain\Service\PhoneNumberValidator;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use App\Users\Domain\Exception\InvalidUserPhoneNumberException;

final class PhoneNumber implements \Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 15;

    private string $phoneNumber;

    /**
     * @throws InvalidUserPhoneNumberException
     */
    public function __construct(string $phoneNumber)
    {
        try {
            $this->phoneNumber = PhoneNumberValidator::validate(phoneNumber: $phoneNumber, maxLength: self::MAX_LENGTH);
        } catch (InvalidPhoneNumberException $e) {
            throw InvalidUserPhoneNumberException::fromBaseException($e);
        }
    }

    public function value(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @throws InvalidUserPhoneNumberException
     */
    public static function fromString(string $email): self
    {
        return new self($email);
    }

    public function __toString(): string
    {
        return $this->value();
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value();
    }
}
