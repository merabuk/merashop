<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\ValueObject\Category\Translation;
use App\Shared\Domain\ValueObject\Locale;
use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'category_translations')]
#[ORM\UniqueConstraint(name: 'uniq_translation_locale', columns: ['category_id', 'locale'])]
class OrmCategoryTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\ManyToOne(targetEntity: OrmCategory::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(
        name: 'category_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: ReferentialAction::CASCADE->value
    )]
    public OrmCategory $category;

    #[ORM\Column(type: Types::STRING, length: Locale::MAX_LENGTH)]
    public string $locale;

    #[ORM\Column(type: Types::STRING, length: Translation::NAME_MAX_LENGTH)]
    public string $name;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $description = null;
}
