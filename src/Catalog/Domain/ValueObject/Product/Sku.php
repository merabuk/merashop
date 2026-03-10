<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Exception\Product\InvalidProductSkuException;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use Stringable;

final readonly class Sku implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 50;

    private string $sku;

    /**
     * @throws InvalidProductSkuException
     */
    public function __construct(string $sku)
    {
        $sku = mb_trim($sku);
        if ('' === $sku) {
            throw InvalidProductSkuException::becauseItIsEmpty();
        }

        $this->sku = $sku;
    }

    public function value(): string
    {
        return $this->sku;
    }

    /**
     * @throws InvalidProductSkuException
     */
    public static function fromString(string $sku): self
    {
        return new self($sku);
    }

    public function __toString(): string
    {
        return $this->sku;
    }

    protected function getPrimitiveValue(): string
    {
        return $this->value();
    }
}
