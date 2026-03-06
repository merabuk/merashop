<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryVersionException;
use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\BaseVersionValueObject;

final readonly class Version extends BaseVersionValueObject
{
    /**
     * @throws InvalidCategoryVersionException
     */
    public function __construct(int $value)
    {
        try {
            parent::__construct($value);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidCategoryVersionException::becauseItIsNotAValidVersion();
        }
    }

    /**
     * @throws InvalidCategoryVersionException
     */
    public static function initial(): self
    {
        return new self(self::getInitialValue());
    }

    /**
     * @throws InvalidCategoryVersionException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
