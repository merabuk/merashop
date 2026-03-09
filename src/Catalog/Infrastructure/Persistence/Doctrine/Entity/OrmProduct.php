<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\ValueObject\Product\Price;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Infrastructure\Persistence\Doctrine\Type\Product\StatusType;
use App\Shared\Domain\Criteria\Sorting\Sort;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\TimestampableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
#[ORM\UniqueConstraint(name: 'uniq_products_ulid', columns: ['ulid'])]
#[ORM\UniqueConstraint(name: 'uniq_products_sku', columns: ['sku'])]
class OrmProduct
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME)]
    public string $ulid;

    #[ORM\Column(type: Types::STRING, length: Sku::MAX_LENGTH)]
    public string $sku;

    #[ORM\Column(type: Types::BIGINT)]
    public int $priceAmount;

    #[ORM\Column(type: Types::STRING, length: Price::CURRENCY_LENGTH)]
    public string $priceCurrency;

    #[ORM\Column(type: StatusType::NAME)]
    public StatusEnum $status = StatusEnum::Draft;

    /**
     * @var Collection<int, OrmProductTranslation>
     */
    #[ORM\OneToMany(
        targetEntity: OrmProductTranslation::class,
        mappedBy: 'product',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    public Collection $translations;

    /**
     * @var Collection<int, OrmCategory>
     */
    #[ORM\ManyToMany(targetEntity: OrmCategory::class, inversedBy: 'products')]
    #[ORM\JoinTable(name: 'product_categories')]
    #[ORM\JoinColumn(
        name: 'product_id',
        referencedColumnName: 'id',
        onDelete: ReferentialAction::CASCADE->value
    )]
    #[ORM\InverseJoinColumn(
        name: 'category_id',
        referencedColumnName: 'id',
        onDelete: ReferentialAction::CASCADE->value
    )]
    public Collection $categories;

    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $version = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $createdBy = null;

    #[ORM\Column(type: UlidType::NAME, nullable: true)]
    public ?string $updatedBy = null;

    /**
     * @var Collection<int, OrmProductAttributeValue>
     */
    #[ORM\OneToMany(
        targetEntity: OrmProductAttributeValue::class,
        mappedBy: 'product',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    public Collection $attributeValues;

    /**
     * @var Collection<int, OrmProductImage>
     */
    #[ORM\OneToMany(
        targetEntity: OrmProductImage::class,
        mappedBy: 'product',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['sortOrder' => Sort::ASC])]
    public Collection $images;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->attributeValues = new ArrayCollection();
        $this->images = new ArrayCollection();
    }
}
