<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\ValueObject\AttributeOption\Code;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\TimestampableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'attribute_options')]
#[ORM\UniqueConstraint(name: 'uniq_attribute_options_ulid', columns: ['ulid'])]
#[ORM\UniqueConstraint(name: 'uniq_attribute_options_attribute_id_code', columns: ['attribute_id', 'code'])]
class OrmAttributeOption
{
    use TimestampableEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $ulid = null;

    #[ORM\ManyToOne(targetEntity: OrmAttribute::class, inversedBy: 'options')]
    #[ORM\JoinColumn(
        name: 'attribute_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: ReferentialAction::CASCADE->value
    )]
    public OrmAttribute $attribute;

    #[ORM\Column(type: Types::STRING, length: Code::MAX_LENGTH)]
    public ?string $code = null;

    /**
     * @var Collection<int, OrmAttributeOptionTranslation>
     */
    #[ORM\OneToMany(
        targetEntity: OrmAttributeOptionTranslation::class,
        mappedBy: 'option',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    public Collection $translations;

    #[ORM\Column(type: Types::BOOLEAN)]
    public ?bool $isActive = null;

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

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function setId(?int $value): void
    {
        $this->id = $value;
    }
}
