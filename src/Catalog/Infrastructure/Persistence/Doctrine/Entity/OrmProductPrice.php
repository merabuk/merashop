<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\ValueObject\ProductPrice\TaxIncludedFlag;
use App\Catalog\Infrastructure\Persistence\Doctrine\Type\ProductPrice\CurrencyType;
use App\Catalog\Infrastructure\Persistence\Doctrine\Type\ProductPrice\TaxTypeType;
use App\Catalog\Infrastructure\Persistence\Doctrine\Type\ProductPrice\TypeType;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Enum\TaxTypeEnum;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\TimestampableEntityTrait;
use DateTimeImmutable;
use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_prices')]
#[ORM\Index(name: 'idx_product_prices_product_id_type_currency', columns: ['product_id', 'type', 'currency'])]
class OrmProductPrice
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrmProduct::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(
        name: 'product_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: ReferentialAction::CASCADE->value
    )]
    public OrmProduct $product;

    #[ORM\Column(type: Types::BIGINT)]
    public ?int $amount = null;

    #[ORM\Column(type: CurrencyType::NAME)]
    public CurrencyEnum $currency = CurrencyEnum::UAH;

    #[ORM\Column(type: TypeType::NAME)]
    public TypeEnum $type = TypeEnum::Regular;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    public ?string $taxValue = null;

    #[ORM\Column(type: TaxTypeType::NAME, nullable: true)]
    public ?TaxTypeEnum $taxType = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $taxIncluded = TaxIncludedFlag::DEFAULT_VALUE;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $validFrom = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $validTo = null;

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
