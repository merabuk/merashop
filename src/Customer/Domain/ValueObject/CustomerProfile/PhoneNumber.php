<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfilePhoneNumberException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;

final readonly class PhoneNumber implements \Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 20;

    private string $phoneNumber;

    /**
     * @throws InvalidCustomerProfilePhoneNumberException
     */
    public function __construct(string $phoneNumber)
    {
        $trimmed = mb_trim($phoneNumber);

        if (empty($trimmed)) {
            throw new InvalidCustomerProfilePhoneNumberException('Phone number cannot be empty');
        }

        if (self::MAX_LENGTH < mb_strlen($trimmed)) {
            throw new InvalidCustomerProfilePhoneNumberException(sprintf('Phone number must be at most %d characters long', self::MAX_LENGTH));
        }

        $this->phoneNumber = $trimmed;
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
