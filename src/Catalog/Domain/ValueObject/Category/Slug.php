<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategorySlugException;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class Slug implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const string REGEX = '/^(?![\d-])(?!.*--)[a-z\d-]+(?<!-)$/';
    public const int MAX_LENGTH = 255;

    private string $slug;

    /**
     * @throws InvalidCategorySlugException
     */
    public function __construct(string $slug)
    {
        $slug = mb_trim($slug);

        $this->ensureIsValidSlug($slug);

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

    /**
     * @throws InvalidCategorySlugException
     */
    private function ensureIsValidSlug(string $slug): void
    {
        if ('' === $slug) {
            throw InvalidCategorySlugException::becauseItIsEmpty();
        }

        if (mb_strlen($slug) > self::MAX_LENGTH) {
            throw InvalidCategorySlugException::becauseItIsTooLong(self::MAX_LENGTH);
        }

        if (!preg_match(self::REGEX, $slug)) {
            throw InvalidCategorySlugException::becauseItDoesNotMatchRegex();
        }
    }
}
