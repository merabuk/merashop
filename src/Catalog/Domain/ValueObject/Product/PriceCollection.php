<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\Product;

use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Exception\Product\InvalidProductPriceItemException;
use App\Catalog\Domain\Exception\Product\ProductPricesEmptyException;
use App\Catalog\Domain\Exception\Product\ProductPriceUniqueException;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Exception\ValueObject\InvalidAbstractCollectionItemException;
use App\Shared\Domain\ValueObject\AbstractCollection;
use DateTimeImmutable;

/**
 * @extends AbstractCollection<ProductPrice>
 */
final readonly class PriceCollection extends AbstractCollection
{
    /**
     * @param array<int, ProductPrice> $items
     *
     * @throws InvalidProductPriceItemException
     * @throws ProductPricesEmptyException
     * @throws ProductPriceUniqueException
     */
    public function __construct(array $items)
    {
        try {
            $this->ensureNotEmpty(items: $items);
            $this->ensureDataType(items: $items);
            $this->ensureUnique(items: $items);
            parent::__construct(items: $items);
        } catch (InvalidAbstractCollectionItemException $e) {
            throw InvalidProductPriceItemException::fromBase($e);
        }
    }

    /**
     * @param array<int, ProductPrice> $items
     *
     * @throws InvalidProductPriceItemException
     * @throws ProductPricesEmptyException
     * @throws ProductPriceUniqueException
     */
    public static function fromArray(array $items): self
    {
        return new self(items: $items);
    }

    public function getByCurrencyAndType(CurrencyEnum $currency, TypeEnum $type): ?ProductPrice
    {
        return array_find($this->items, fn (ProductPrice $p) => $p->getPrice()->getCurrency() === $currency
            && $p->getType()->value() === $type
        );
    }

    public function findActiveForCurrency(CurrencyEnum $currency, DateTimeImmutable $now): ?ProductPrice
    {
        $active = array_filter(
            $this->items,
            fn (ProductPrice $p) => $p->getPrice()->getCurrency() === $currency && $p->isActive($now)
        );

        return array_find($active, fn (ProductPrice $p) => $p->getType()->isSale())
            ?? array_find($active, fn (ProductPrice $p) => $p->getType()->isRegular());
    }

    /**
     * @param ProductPrice[] $items
     *
     * @throws ProductPricesEmptyException
     */
    private function ensureNotEmpty(array $items): void
    {
        if (empty($items)) {
            throw ProductPricesEmptyException::becauseItIsEmpty();
        }
    }

    /**
     * @param ProductPrice[] $items
     *
     * @throws ProductPriceUniqueException
     */
    private function ensureUnique(array $items): void
    {
        $keys = [];
        foreach ($items as $price) {
            $priceType = $price->getType()->asString();
            $currency = $price->getPrice()->getCurrencyCode();
            $key = sprintf('%s_%s', $priceType, $currency);
            if (isset($keys[$key])) {
                throw ProductPriceUniqueException::becauseDuplicatePriceTypeForCurrency(priceType: $priceType, currency: $currency);
            }
            $keys[$key] = true;
        }
    }

    protected function getExpectedClass(): string
    {
        return ProductPrice::class;
    }
}
