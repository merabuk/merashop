<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Attribute;

use App\Catalog\Domain\Exception\Attribute\InvalidAttributeCodeException;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class Code implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 50;

    private string $code;

    /**
     * @throws InvalidAttributeCodeException
     */
    public function __construct(string $code)
    {
        $code = mb_trim($code);
        if ('' === $code) {
            throw InvalidAttributeCodeException::becauseItIsEmpty();
        }

        $this->code = $code;
    }

    public function value(): string
    {
        return $this->code;
    }

    /**
     * @throws InvalidAttributeCodeException
     */
    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function __toString(): string
    {
        return $this->code;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value();
    }
}
