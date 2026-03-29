<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Unit\Infrastructure\Persistence\Doctrine\Mapper;

use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\OrmProductPrice;
use App\Catalog\Infrastructure\Persistence\Doctrine\Mapper\ProductPriceMapper;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Enum\TaxTypeEnum;
use App\Shared\Domain\Exception\Mappers\EntityIdMissingException;
use App\Tests\Catalog\Support\ProductPriceMother;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class ProductPriceMapperTest extends TestCase
{
    private ProductPriceMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ProductPriceMapper();
    }

    public function testToDomain(): void
    {
        $clock = new MockClock('2024-12-24 00:00:00');

        $orm = new OrmProductPrice();
        $orm->setId(123);
        $orm->amount = 1000;
        $orm->currency = CurrencyEnum::UAH;
        $orm->type = TypeEnum::Sale;
        $orm->taxValue = '1.50';
        $orm->taxType = TaxTypeEnum::Percentage;
        $orm->taxIncluded = true;
        $orm->validFrom = $clock->now();
        $orm->validTo = $clock->now()->modify('+1 month');
        $orm->version = 1;
        $orm->createdBy = ProductPriceMother::DEFAULT_ADMIN_ULID;
        $orm->updatedBy = ProductPriceMother::DEFAULT_ADMIN_ULID;

        $domain = $this->mapper->toDomain($orm);

        self::assertSame($orm->id, $domain->getId()->value());
        self::assertSame($orm->amount, $domain->getPrice()->getAmount());
        self::assertSame($orm->currency, $domain->getPrice()->getCurrency());
        self::assertSame($orm->type, $domain->getType()->value());
        self::assertSame((float) $orm->taxValue, $domain->getTax()->getValue());
        self::assertSame($orm->taxType, $domain->getTax()->getType());
        self::assertSame($orm->taxIncluded, $domain->getTaxIncluded()->value());
        self::assertSame($orm->validFrom, $domain->getValidityPeriod()->getFrom()->value());
        self::assertSame($orm->validTo, $domain->getValidityPeriod()->getTo()->value());
        self::assertSame($orm->version, $domain->getVersion()->value());
        self::assertSame($orm->createdBy, $domain->getCreatedBy()->value());
        self::assertSame($orm->updatedBy, $domain->getUpdatedBy()->value());
    }

    public function testMapToDomainThrowsExceptionWhenOrmMissingId(): void
    {
        $orm = new OrmProductPrice();

        $this->expectException(EntityIdMissingException::class);

        $this->mapper->toDomain($orm);
    }

    public function testMapToExistingOrm(): void
    {
        $domain = ProductPriceMother::createWithData(
            currency: CurrencyEnum::USD,
            type: TypeEnum::Sale,
            id: 123
        );
        $orm = new OrmProductPrice();
        $orm->currency = CurrencyEnum::UAH;
        $orm->type = TypeEnum::Regular;

        $this->mapper->mapToExistingOrm($domain, $orm);

        self::assertNull($orm->id);
        self::assertSame($domain->getPrice()->getAmount(), $orm->amount);
        self::assertSame(CurrencyEnum::UAH, $orm->currency);
        self::assertSame(TypeEnum::Regular, $orm->type);
        self::assertSame((string) $domain->getTax()->getValue(), $orm->taxValue);
        self::assertSame($domain->getTax()->getType(), $orm->taxType);
        self::assertSame($domain->getTaxIncluded()->value(), $orm->taxIncluded);
        self::assertSame($domain->getValidityPeriod()->getFrom()->value(), $orm->validFrom);
        self::assertSame($domain->getValidityPeriod()->getTo()->value(), $orm->validTo);
    }
}
