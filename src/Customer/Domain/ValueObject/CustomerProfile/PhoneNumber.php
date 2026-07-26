<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfilePhoneNumberException;
use App\Shared\Domain\Exception\Services\Validation\InvalidPhoneNumberException;
use App\Shared\Domain\Service\Validation\PhoneNumberValidator;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class PhoneNumber implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 20;

    private string $phoneNumber;

    /**
     * @throws InvalidCustomerProfilePhoneNumberException
     */
    public function __construct(string $phoneNumber)
    {
        try {
            $this->phoneNumber = PhoneNumberValidator::validate($phoneNumber, self::MAX_LENGTH);
        } catch (InvalidPhoneNumberException $e) {
            throw InvalidCustomerProfilePhoneNumberException::fromBase($e);
        }
    }

    public function value(): string
    {
        return $this->phoneNumber;
    }

    /**
     * @throws InvalidCustomerProfilePhoneNumberException
     */
    public static function fromString(string $phoneNumber): self
    {
        return new self($phoneNumber);
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
