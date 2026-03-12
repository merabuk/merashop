<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceTypeException;
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

    public static function regular(): self
    {
        return self::fromEnum(TypeEnum::Regular);
    }

    public static function sale(): self
    {
        return self::fromEnum(TypeEnum::Sale);
    }

    public static function cost(): self
    {
        return self::fromEnum(TypeEnum::Cost);
    }

    /**
     * @throws InvalidProductPriceTypeException
     */
    public static function fromString(string $type): self
    {
        $enum = TypeEnum::tryFrom(mb_trim($type));

        if (null === $enum) {
            throw InvalidProductPriceTypeException::becauseItIsNotAValidType(invalidValue: $type, availableValues: TypeEnum::getValues());
        }

        return new self($enum);
    }

    public function value(): TypeEnum
    {
        return $this->type;
    }

    public function isRegular(): bool
    {
        return TypeEnum::Regular === $this->type;
    }

    public function isSale(): bool
    {
        return TypeEnum::Sale === $this->type;
    }

    public function isCost(): bool
    {
        return TypeEnum::Cost === $this->type;
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
