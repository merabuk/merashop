<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategorySlugException;
use App\Shared\Domain\ValueObject\EquatableInterface;
use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class Slug implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $slug;

    /**
     * @throws InvalidCategorySlugException
     */
    public function __construct(string $slug)
    {
        $slug = mb_trim($slug);
        if ('' === $slug) {
            throw InvalidCategorySlugException::becauseItIsEmpty();
        }

        $this->slug = $slug;
    }

    public function value(): string
    {
        return $this->slug;
    }

    /**
     * @throws InvalidCategorySlugException
     */
    public static function fromString(string $slug): self
    {
        return new self($slug);
    }

    public function __toString(): string
    {
        return $this->slug;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value();
    }
}
