<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeTypeException;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class Type implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    private TypeEnum $type;

    private function __construct(TypeEnum $type)
    {
        $this->type = $type;
    }

    public static function fromEnum(TypeEnum $type): self
    {
        return new self($type);
    }

    public static function string(): self
    {
        return self::fromEnum(TypeEnum::String);
    }

    public static function int(): self
    {
        return self::fromEnum(TypeEnum::Int);
    }

    public static function boolean(): self
    {
        return self::fromEnum(TypeEnum::Boolean);
    }

    public static function select(): self
    {
        return self::fromEnum(TypeEnum::Select);
    }

    /**
     * @throws InvalidAttributeTypeException
     */
    public static function fromString(string $type): self
    {
        $enum = TypeEnum::tryFrom(mb_trim($type));

        if (null === $enum) {
            throw InvalidAttributeTypeException::becauseItIsNotAValidType(invalidValue: $type, availableValues: TypeEnum::getValues());
        }

        return new self($enum);
    }

    public function value(): TypeEnum
    {
        return $this->type;
    }

    public function isString(): bool
    {
        return TypeEnum::String === $this->type;
    }

    public function isInt(): bool
    {
        return TypeEnum::Int === $this->type;
    }

    public function isBoolean(): bool
    {
        return TypeEnum::Boolean === $this->type;
    }

    public function isSelect(): bool
    {
        return TypeEnum::Select === $this->type;
    }

    public function __toString(): string
    {
        return $this->value()->value;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value()->value;
    }
}
