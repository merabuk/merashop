<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Catalog\Domain\Exception\Category\InvalidCategoryPathException;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class Path implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const string SEPARATOR = '/';
    public const int MAX_LENGTH = 255;

    private string $value;

    /**
     * @throws InvalidCategoryPathException
     */
    public function __construct(string $path)
    {
        $path = mb_trim($path);

        $this->ensureIsValidPath($path);

        $this->value = $path;
    }

    public function value(): string
    {
        return $this->value;
    }

    /**
     * @throws InvalidCategoryPathException
     */
    public static function fromString(string $path): self
    {
        return new self($path);
    }

    /**
     * @throws InvalidCategoryPathException
     */
    public static function generate(Slug $slug, ?self $parentPath = null): self
    {
        if (null === $parentPath) {
            return self::root($slug);
        }

        $newPath = mb_rtrim($parentPath->value(), self::SEPARATOR);
        $newPath .= self::SEPARATOR;
        $newPath .= mb_ltrim($slug->value(), self::SEPARATOR);

        return new self($newPath);
    }

    /**
     * @throws InvalidCategoryPathException
     */
    public static function root(Slug $slug): self
    {
        return new self(self::SEPARATOR.$slug->value());
    }

    public function startsWith(self $other): bool
    {
        return str_starts_with($this->value, $other->value().self::SEPARATOR);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value();
    }

    /**
     * @throws InvalidCategoryPathException
     */
    private function ensureIsValidPath(string $path): void
    {
        if ('' === $path) {
            throw InvalidCategoryPathException::becauseItIsEmpty();
        }

        $separator = preg_quote(self::SEPARATOR, '/');

        if (preg_match(sprintf('/ |%s{2,}/', $separator), $path, $matches)) {
            throw InvalidCategoryPathException::becauseItContainsInvalidCharacters();
        }

        if (!preg_match(sprintf('/^%s/', $separator), $path, $matches)) {
            throw InvalidCategoryPathException::becauseItDoesNotStartWithSeparator(self::SEPARATOR);
        }

        if (mb_strlen($path) > self::MAX_LENGTH) {
            throw InvalidCategoryPathException::becauseItIsTooLong(self::MAX_LENGTH);
        }
    }
}
