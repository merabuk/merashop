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
    public const int MIN_LENGTH = 8;
    public const string REGEX = '/^(?!-{2,}|^-|-$|^\d)[A-Z\d-]+$/';

    private string $sku;

    /**
     * @throws InvalidProductSkuException
     */
    public function __construct(string $sku)
    {
        $sku = mb_trim($sku);

        $this->ensureIsValidSku($sku);

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

    /**
     * @throws InvalidProductSkuException
     */
    private function ensureIsValidSku(string $sku): void
    {
        if ('' === $sku) {
            throw InvalidProductSkuException::becauseItIsEmpty();
        }

        if (!preg_match(self::REGEX, $sku)) {
            throw InvalidProductSkuException::becauseItIsInvalidFormat();
        }

        $length = mb_strlen($sku);

        if ($length < self::MIN_LENGTH) {
            throw InvalidProductSkuException::becauseToShort(self::MIN_LENGTH);
        }

        if ($length > self::MAX_LENGTH) {
            throw InvalidProductSkuException::becauseItIsTooLong(self::MAX_LENGTH);
        }
    }
}
