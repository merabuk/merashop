<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryPathException;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class Path implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $path;

    /**
     * @throws InvalidCategoryPathException
     */
    public function __construct(string $path)
    {
        $path = mb_trim($path);

        if ('' === $path) {
            throw InvalidCategoryPathException::becauseItIsEmpty();
        }

        if (mb_strlen($path) > self::MAX_LENGTH) {
            throw InvalidCategoryPathException::becauseItIsTooLong(self::MAX_LENGTH);
        }

        $this->path = $path;
    }

    public function value(): string
    {
        return $this->path;
    }

    /**
     * @throws InvalidCategoryPathException
     */
    public static function fromString(string $path): self
    {
        return new self($path);
    }

    public function __toString(): string
    {
        return $this->path;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value();
    }
}
