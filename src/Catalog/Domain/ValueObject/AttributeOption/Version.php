<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\AttributeOption;

use App\Catalog\Domain\Exception\AttributeOption\InvalidAttributeOptionVersionException;
use App\Shared\Domain\Exception\Services\IntegerIsNotUnsignedException;
use App\Shared\Domain\ValueObject\Temporal\BaseVersionValueObject;

final readonly class Version extends BaseVersionValueObject
{
    /**
     * @throws InvalidAttributeOptionVersionException
     */
    public function __construct(int $value)
    {
        try {
            parent::__construct($value);
        } catch (IntegerIsNotUnsignedException) {
            throw InvalidAttributeOptionVersionException::becauseItIsNotAValidVersion();
        }
    }

    /**
     * @throws InvalidAttributeOptionVersionException
     */
    public static function initial(): self
    {
        return new self(self::getInitialValue());
    }

    /**
     * @throws InvalidAttributeOptionVersionException
     */
    public static function fromInt(int $id): self
    {
        return new self($id);
    }
}
