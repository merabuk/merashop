<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Category;

use App\Shared\Domain\ValueObject\ValueObjectEqualityTrait;
use Stringable;

final class Path implements Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function value(): string
    {
        return $this->path;
    }

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
