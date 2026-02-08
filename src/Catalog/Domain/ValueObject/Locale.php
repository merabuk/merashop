<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject;

use App\Catalog\Domain\Exception\InvalidLocaleException;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class Locale implements Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 7;

    private string $value;

    public function __construct(string $value)
    {
        $value = mb_trim($value);
        if ('' === $value) {
            throw InvalidLocaleException::becauseItIsEmpty();
        }

        if (strlen($value) > self::MAX_LENGTH) {
            throw InvalidLocaleException::becauseItIsTooLong(self::MAX_LENGTH);
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value();
    }
}
