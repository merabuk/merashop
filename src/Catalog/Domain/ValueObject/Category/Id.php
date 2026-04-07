<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryIdException;
use App\Shared\Domain\Exception\Services\Validation\IntegerIsNotUnsignedException;
use App\Shared\Domain\Service\Validation\IntegerValidator;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\IdInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

final readonly class Id implements EquatableInterface, IdInterface
{
    use ValueObjectEqualityTrait;

    private int $id;

    /**
     * @throws InvalidCategoryIdException
     */
    public function __construct(int $id)
    {
        try {
            $this->id = IntegerValidator::validateUnsigned($id);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidCategoryIdException::becauseItIsNotAValidId();
        }
    }

    public function value(): int
    {
        return $this->id;
    }

    /**
     * @throws InvalidCategoryIdException
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
