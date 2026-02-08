<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\ValueObject\Locale;
use App\Catalog\Domain\ValueObject\Name;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'catalog_product_translation')]
#[ORM\UniqueConstraint(name: 'uniq_product_translation_locale', columns: ['product_id', 'locale'])]
class OrmProductTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrmProduct::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public OrmProduct $product;

    #[ORM\Column(type: Types::STRING, length: Locale::MAX_LENGTH)]
    public string $locale;

    #[ORM\Column(type: Types::STRING, length: Name::MAX_LENGTH)]
    public string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $description = null;
}
