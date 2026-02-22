<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use App\Catalog\Domain\Exception\ProductAttribute\InvalidProductAttributeIdException;
use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\IntegerValidator;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class Id implements Stringable
{
    use ValueObjectEqualityTrait;

    private int $id;

    /**
     * @throws InvalidProductAttributeIdException
     */
    public function __construct(int $id)
    {
        try {
            $this->id = IntegerValidator::validateUnsigned($id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidProductAttributeIdException::becauseItIsNotAValidId();
        }
    }

    public function value(): int
    {
        return $this->id;
    }

    /**
     * @throws InvalidProductAttributeIdException
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
