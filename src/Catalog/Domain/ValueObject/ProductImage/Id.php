<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductImage;

use App\Catalog\Domain\Exception\ProductImage\InvalidProductImageIdException;
use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\Validation\IntegerValidator;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class Id implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    private int $id;

    /**
     * @throws InvalidProductImageIdException
     */
    public function __construct(int $id)
    {
        try {
            $this->id = IntegerValidator::validateUnsigned($id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidProductImageIdException::becauseItIsNotAValidId();
        }
    }

    public function value(): int
    {
        return $this->id;
    }

    /**
     * @throws InvalidProductImageIdException
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
