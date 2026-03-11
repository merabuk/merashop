<?php

declare(strict_types=1);

namespace App\Customer\Domain\ValueObject\CustomerProfile;

use App\Customer\Domain\Exception\CustomerProfile\InvalidCustomerProfileIdException;
use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\Validation\IntegerValidator;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final class Id implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    private int $id;

    /**
     * @throws InvalidCustomerProfileIdException
     */
    public function __construct(int $id)
    {
        try {
            $this->id = IntegerValidator::validateUnsigned($id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidCustomerProfileIdException::becauseItIsNotAValidId();
        }
    }

    public function value(): int
    {
        return $this->id;
    }

    /**
     * @throws InvalidCustomerProfileIdException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    protected function getPrimitiveValue(): int
    {
        return $this->value();
    }
}
