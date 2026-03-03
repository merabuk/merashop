<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeVersionException;
use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\IntegerValidator;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class Version implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    private int $value;

    /**
     * @throws InvalidAttributeVersionException
     */
    public function __construct(int $value)
    {
        try {
            $this->value = IntegerValidator::validateUnsigned($value);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidAttributeVersionException::becauseItIsNotAValidId();
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    /**
     * @throws InvalidAttributeVersionException
     */
    public static function initial(): self
    {
        return new self(1);
    }

    /**
     * @throws InvalidAttributeVersionException
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
