<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Domain\ValueObject\Product;

use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Exception\Product\InvalidProductPriceItemException;
use App\Catalog\Domain\Exception\Product\ProductPricesEmptyException;
use App\Catalog\Domain\Exception\Product\ProductPriceUniqueException;
use App\Catalog\Domain\ValueObject\Product\PriceCollection;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Tests\Catalog\Support\ProductPriceMother;
use App\Tests\Shared\BaseUnitTest;
use App\Tests\Shared\Unit\Domain\ValueObject\Traits\ValueObjectEqualityCheckTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Symfony\Component\Clock\MockClock;
use Traversable;

final class PriceCollectionTest extends BaseUnitTest
{
    use ValueObjectEqualityCheckTrait;

    public function testItCreatesValidPriceCollection(): void
    {
        $prices = self::getValidPrices();

        $vo = PriceCollection::fromArray($prices);

        self::assertCount(count($prices), $vo);
        self::assertInstanceOf(Traversable::class, $vo->getIterator());
        foreach ($vo->all() as $price) {
            self::assertInstanceOf(ProductPrice::class, $price);
        }
    }

    public function testItProvidesEqualityCheck(): void
    {
        $prices = self::getValidPrices();

        $this->assertArrayVOProvidesEqualityCheck(
            className: PriceCollection::class,
            value: $prices,
            shuffledValue: array_reverse($prices),
            anotherValue: [$prices[0]],
        );
    }

    #[DataProvider('invalidPriceCollectionProvider')]
    public function testThrowsExceptionOnInvalidInput(array $invalidValue, string $exceptionClass): void
    {
        $this->expectException($exceptionClass);
        PriceCollection::fromArray($invalidValue);
    }

    public static function invalidPriceCollectionProvider(): iterable
    {
        yield 'empty' => [
            'invalidValue' => [],
            'exceptionClass' => ProductPricesEmptyException::class,
        ];
        yield 'invalid type in array' => [
            'invalidValue' => [new stdClass()],
            'exceptionClass' => InvalidProductPriceItemException::class,
        ];
        yield 'not unique prices' => [
            'invalidValue' => [
                ProductPriceMother::createWithData(currency: CurrencyEnum::UAH, type: TypeEnum::Regular),
                ProductPriceMother::createWithData(currency: CurrencyEnum::UAH, type: TypeEnum::Regular),
            ],
            'exceptionClass' => ProductPriceUniqueException::class,
        ];
    }

    public function testItGetsByCurrencyAndType(): void
    {
        $prices = self::getValidPrices();
        $vo = PriceCollection::fromArray($prices);

        foreach ($prices as $price) {
            $foundPrice = $vo->getByCurrencyAndType($price->getPrice()->getCurrency(), $price->getType()->value());
            self::assertNotNull($foundPrice);
            self::assertSame($price, $foundPrice);
        }
    }

    public function testItFindsActivePriceForCurrency(): void
    {
        $clock = new MockClock('2024-01-01 10:00:00');
        $currency = CurrencyEnum::UAH;

        $regularPrice = ProductPriceMother::createWithData(currency: $currency, type: TypeEnum::Regular);
        $salePrice = ProductPriceMother::createWithData(
            currency: $currency,
            type: TypeEnum::Sale,
            validFrom: $clock->now()->modify('+1 day'),
            validTo: $clock->now()->modify('+31 days'),
        );

        $vo = PriceCollection::fromArray([$regularPrice, $salePrice]);

        self::assertNull($vo->findActiveForCurrency(currency: CurrencyEnum::USD, now: $clock->now()));
        self::assertSame($regularPrice, $vo->findActiveForCurrency(currency: $currency, now: $clock->now()));
        self::assertSame($salePrice, $vo->findActiveForCurrency(
            currency: $currency,
            now: $clock->now()->modify('+2 days'),
        ));
    }

    /**
     * @return ProductPrice[]
     */
    private static function getValidPrices(): array
    {
        $currencies = CurrencyEnum::cases();
        $productPriceTypes = TypeEnum::cases();

        $prices = [];
        foreach ($currencies as $currency) {
            foreach ($productPriceTypes as $type) {
                $prices[] = ProductPriceMother::createWithData(currency: $currency, type: $type);
            }
        }

        return $prices;
    }
}
