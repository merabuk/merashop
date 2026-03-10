<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Exception\ValueObject\InvalidLocaleException;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final class Locale implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 7;

    private string $value;

    /**
     * @throws InvalidLocaleException
     */
    public function __construct(string $value)
    {
        $value = mb_trim($value);

        if ('' === $value) {
            throw InvalidLocaleException::becauseItIsEmpty();
        }

        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw InvalidLocaleException::becauseItIsTooLong(self::MAX_LENGTH);
        }

        if (!LocaleEnum::tryFrom($value)) {
            throw InvalidLocaleException::becauseItIsNotValid();
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * @throws InvalidLocaleException
     */
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
