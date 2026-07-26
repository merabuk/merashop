<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileFirstNameException;
use App\Shared\Domain\Exception\Services\Validation\InvalidStringException;
use App\Shared\Domain\Service\Validation\StringValidator;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use App\Shared\Domain\ValueObject\Contract\ValueObjectInterface;

final readonly class FirstName implements ValueObjectInterface
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 64;

    private string $name;

    /**
     * @throws InvalidCustomerProfileFirstNameException
     */
    public function __construct(string $name)
    {
        try {
            $this->name = StringValidator::validate(rawValue: $name, maxLength: self::MAX_LENGTH);
        } catch (InvalidStringException $e) {
            throw InvalidCustomerProfileFirstNameException::fromBaseException($e);
        }
    }

    public function value(): string
    {
        return $this->name;
    }

    /**
     * @throws InvalidCustomerProfileFirstNameException
     */
    public static function fromString(string $name): self
    {
        return new self($name);
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
