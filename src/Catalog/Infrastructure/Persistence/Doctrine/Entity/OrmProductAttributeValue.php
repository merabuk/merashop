<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'product_attribute_values')]
#[ORM\Index(name: 'idx_product_attribute_values_product_id', columns: ['product_id'])]
#[ORM\Index(name: 'idx_product_attribute_values_attribute_id', columns: ['attribute_id'])]
#[ORM\Index(name: 'idx_product_attribute_values_option_id', columns: ['option_id'])]
class OrmProductAttributeValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrmProduct::class, inversedBy: 'attributeValues')]
    #[ORM\JoinColumn(
        name: 'product_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: ReferentialAction::CASCADE->value,
        options: ['foreignKey' => ['name' => 'fk_product_attribute_values_product_id']]
    )]
    public OrmProduct $product;

    #[ORM\ManyToOne(targetEntity: OrmAttribute::class)]
    #[ORM\JoinColumn(
        name: 'attribute_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: ReferentialAction::CASCADE->value,
        options: ['foreignKey' => ['name' => 'fk_product_attribute_values_attribute_id']]
    )]
    public OrmAttribute $attribute;

    #[ORM\ManyToOne(targetEntity: OrmAttributeOption::class)]
    #[ORM\JoinColumn(
        name: 'option_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: ReferentialAction::CASCADE->value,
        options: ['foreignKey' => ['name' => 'fk_product_attribute_values_option_id']]
    )]
    public ?OrmAttributeOption $option = null;

    /**
     * @var ?array<string, mixed>
     */
    #[ORM\Column(type: Types::JSONB, nullable: true)]
    public ?array $valueJson = null;

    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $version = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $createdBy = null;

    #[ORM\Column(type: UlidType::NAME, nullable: true)]
    public ?string $updatedBy = null;

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
