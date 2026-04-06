<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\ValueObject\ProductImage\MainImageFlag;
use App\Catalog\Domain\ValueObject\ProductImage\SortOrder;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\TimestampableEntityTrait;
use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'product_images')]
#[ORM\UniqueConstraint(name: 'uniq_product_images_ulid', columns: ['ulid'])]
class OrmProductImage
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $ulid = null;

    #[ORM\ManyToOne(targetEntity: OrmProduct::class, inversedBy: 'images')]
    #[ORM\JoinColumn(
        name: 'product_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: ReferentialAction::CASCADE->value
    )]
    public OrmProduct $product;

    #[ORM\Column(type: Types::STRING, length: 511)]
    public ?string $path = null;

    #[ORM\Column(type: Types::INTEGER)]
    public ?int $sortOrder = SortOrder::DEFAULT_VALUE;

    #[ORM\Column(type: Types::BOOLEAN)]
    public ?bool $isMain = MainImageFlag::DEFAULT_VALUE;

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
