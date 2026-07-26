<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\ValueObject\Attribute\Translation;
use App\Shared\Domain\ValueObject\Locale;
use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'attribute_translations')]
#[ORM\UniqueConstraint(name: 'uniq_attribute_translations_attribute_id_locale', columns: ['attribute_id', 'locale'])]
class OrmAttributeTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrmAttribute::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(
        name: 'attribute_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: ReferentialAction::CASCADE->value
    )]
    public ?OrmAttribute $attribute = null;

    #[ORM\Column(type: Types::STRING, length: Locale::MAX_LENGTH)]
    public ?string $locale = null;

    #[ORM\Column(type: Types::STRING, length: Translation::NAME_MAX_LENGTH)]
    public ?string $name = null;
}
