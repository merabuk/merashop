<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Exception\Attribute\InvalidAttributeTypeException;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class Type implements EquatableInterface, Stringable
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

    public function is(TypeEnum $enum): bool
    {
        return $this->type === $enum;
    }

    public function hasOptions(): bool
    {
        return $this->type->hasOptions();
    }

    public function hasOptionMetadata(): bool
    {
        return match ($this->type) {
            TypeEnum::Dimension => true,
            default => false,
        };
    }

    public function allowChange(self $newType): bool
    {
        if ($this->equals($newType)) {
            return true;
        }

        return match ($newType->value()) {
            TypeEnum::String => $this->is(TypeEnum::Text),
            TypeEnum::Text => $this->is(TypeEnum::String),
            default => false,
        };
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
