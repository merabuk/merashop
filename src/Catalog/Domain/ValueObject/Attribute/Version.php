<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeVersionException;
use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Temporal\BaseVersionValueObject;

final readonly class Version extends BaseVersionValueObject
{
    /**
     * @throws InvalidAttributeVersionException
     */
    public function __construct(int $value)
    {
        try {
            parent::__construct($value);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidAttributeVersionException::becauseItIsNotAValidVersion();
        }
    }

    /**
     * @throws InvalidAttributeVersionException
     */
    public static function initial(): self
    {
        return new self(self::getInitialValue());
    }

    /**
     * @throws InvalidAttributeVersionException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
