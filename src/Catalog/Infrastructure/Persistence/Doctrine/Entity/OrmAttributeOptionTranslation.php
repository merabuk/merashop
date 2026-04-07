<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\ValueObject\Attribute\Translation;
use App\Shared\Domain\ValueObject\Locale;
use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'attribute_option_translations')]
#[ORM\UniqueConstraint(name: 'uniq_attribute_option_translations_option_id_locale', columns: ['option_id', 'locale'])]
class OrmAttributeOptionTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrmAttributeOption::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(
        name: 'option_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: ReferentialAction::CASCADE->value
    )]
    public OrmAttributeOption $option;

    #[ORM\Column(type: Types::STRING, length: Locale::MAX_LENGTH)]
    public string $locale;

    #[ORM\Column(type: Types::STRING, length: Translation::NAME_MAX_LENGTH)]
    public string $value;
}
