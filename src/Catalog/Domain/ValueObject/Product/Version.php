<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductVersionException;
use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Temporal\BaseVersionValueObject;

final readonly class Version extends BaseVersionValueObject
{
    /**
     * @throws InvalidProductVersionException
     */
    public function __construct(int $value)
    {
        try {
            parent::__construct($value);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidProductVersionException::becauseItIsNotAValidVersion();
        }
    }

    /**
     * @throws InvalidProductVersionException
     */
    public static function initial(): self
    {
        return new self(self::getInitialValue());
    }

    /**
     * @throws InvalidProductVersionException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
