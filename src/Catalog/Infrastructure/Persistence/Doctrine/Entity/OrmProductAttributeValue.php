<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_attribute_values')]
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
        onDelete: ReferentialAction::CASCADE->value
    )]
    public OrmProduct $product;

    #[ORM\ManyToOne(targetEntity: OrmAttribute::class)]
    #[ORM\JoinColumn(
        name: 'attribute_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: ReferentialAction::CASCADE->value
    )]
    public OrmAttribute $attribute;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public ?string $valueString = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $valueInt = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    public ?bool $valueBoolean = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: Types::JSONB, nullable: true)]
    public ?array $valueJson = null;
}
