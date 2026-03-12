<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceValidFromException;
use App\Shared\Domain\ValueObject\Temporal\DateTimeValueObject;
use DateTimeImmutable;
use Throwable;

final readonly class ValidFrom extends DateTimeValueObject
{
    public static function fromDateTime(DateTimeImmutable $date): self
    {
        return new self($date);
    }

    /**
     * @throws InvalidProductPriceValidFromException
     */
    public static function fromString(string $date): self
    {
        try {
            return new self(new DateTimeImmutable($date));
        } catch (Throwable $e) {
            throw InvalidProductPriceValidFromException::becauseItIsNotValidDateTimeString($e);
        }
    }
}
