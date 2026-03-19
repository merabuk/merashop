<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_attribute_values')]
#[ORM\Index(name: 'idx_product_attribute_values_product_id', columns: ['product_id'])]
#[ORM\Index(name: 'idx_product_attribute_values_attribute_id', columns: ['attribute_id'])]
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

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSONB)]
    public ?array $valueJson = null;

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
