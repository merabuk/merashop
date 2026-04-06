<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\ProductPrice;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\Factory\Contract\ProductPriceFactoryInterface;
use App\Catalog\Domain\ValueObject\AdminUlid;
use App\Catalog\Domain\ValueObject\ProductPrice\Id;
use App\Catalog\Domain\ValueObject\ProductPrice\Price;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Domain\ValueObject\ProductPrice\Type;
use App\Catalog\Domain\ValueObject\ProductPrice\ValidityPeriod;
use App\Catalog\Domain\ValueObject\ProductPrice\Version;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Enum\TaxTypeEnum;
use App\Shared\Domain\Service\Identity\UlidGeneratorInterface;
use App\Tests\Shared\Support\Traits\DateTimeHelperTrait;
use DateTimeImmutable;
use Faker\Generator;

final readonly class ProductPriceMother
{
    use DateTimeHelperTrait;

    public const string DEFAULT_ADMIN_ULID = '01KHVRCA679BJ6PBXX5N3G6RR5';

    public function __construct(
        private ProductPriceFactoryInterface $productPriceFactory,
        private UlidGeneratorInterface $ulidGenerator,
        private Generator $faker,
    ) {
    }

    public static function createWithData(
        ?int $amount = null,
        ?CurrencyEnum $currency = null,
        ?TypeEnum $type = null,
        ?float $taxValue = null,
        ?TaxTypeEnum $taxType = null,
        ?bool $taxIncluded = null,
        ?DateTimeImmutable $validFrom = null,
        ?DateTimeImmutable $validTo = null,
        ?int $version = null,
        ?string $createdByUlid = null,
        ?string $updatedByUlid = null,
        ?int $id = null,
    ): ProductPrice {
        $taxType ??= TaxTypeEnum::Percentage;

        if (TypeEnum::Sale === $type) {
            $validFrom ??= new DateTimeImmutable('+1 day');
            $validTo ??= $validFrom->modify('+1 month');
        }

        return new ProductPrice(
            price: new Price(
                amount: $amount ?? random_int(100, 1000),
                currency: $currency ?? CurrencyEnum::UAH,
            ),
            type: Type::fromEnum($type ?? TypeEnum::Regular),
            tax: new Tax(
                value: $taxValue ?? (TaxTypeEnum::Percentage === $taxType ? random_int(1, 10) : random_int(100, 1000)),
                type: $taxType
            ),
            taxIncluded: TaxIncludedFlag::fromBool($taxIncluded ?? true),
            version: $version ? Version::fromInt($version) : Version::initial(),
            createdBy: AdminUlid::fromString($createdByUlid ?? self::DEFAULT_ADMIN_ULID),
            validityPeriod: $validFrom && $validTo
                ? ValidityPeriod::fromDateTimeRange($validFrom, $validTo)
                : null,
            updatedBy: $updatedByUlid ? AdminUlid::fromString($updatedByUlid) : null,
            id: $id ? Id::fromInt($id) : null,
        );
    }

    public function create(
        ?int $amount = null,
        ?CurrencyEnum $currency = null,
        ?TypeEnum $type = null,
        ?float $taxValue = null,
        ?TaxTypeEnum $taxType = null,
        ?bool $taxIncluded = null,
        ?DateTimeImmutable $validFrom = null,
        ?DateTimeImmutable $validTo = null,
        ?string $createdByUlid = null,
    ): ProductPrice {
        $type ??= $this->faker->randomElement(TypeEnum::cases());

        if (TypeEnum::Sale === $type) {
            $validFrom ??= $this->toDateTimeImmutable(
                $this->faker->dateTimeBetween('-1 year', '+1 year')
            );
            $validTo ??= $this->toDateTimeImmutable(
                $this->faker->dateTimeBetween($validFrom->format($validFrom::ATOM), '+2 years')
            );
        }

        return $this->productPriceFactory->createForTest(
            amount: $amount ?? random_int(100, 1000),
            currency: $currency ?? $this->faker->randomElement(CurrencyEnum::cases()),
            type: $type,
            taxValue: $taxValue ?? random_int(1, 100) / 100,
            taxType: $taxType ?? $this->faker->randomElement(TaxTypeEnum::cases()),
            taxIncluded: $taxIncluded ?? $this->faker->boolean(),
            validFrom: $validFrom,
            validTo: $validTo,
            createdByUlid: $createdByUlid ?? $this->ulidGenerator->next(),
        );
    }
}
