<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\ValueObject\Attribute\Code;
use App\Catalog\Infrastructure\Persistence\Doctrine\Type\Attribute\TypeType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'attributes')]
#[ORM\UniqueConstraint(name: 'uniq_attributes_ulid', columns: ['ulid'])]
#[ORM\UniqueConstraint(name: 'uniq_attributes_code', columns: ['code'])]
class OrmAttribute
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME)]
    public string $ulid;

    #[ORM\Column(type: Types::STRING, length: Code::MAX_LENGTH)]
    public string $code;

    #[ORM\Column(type: TypeType::NAME)]
    public TypeEnum $type;

    /**
     * @var Collection<int, OrmAttributeTranslation>
     */
    #[ORM\OneToMany(
        targetEntity: OrmAttributeTranslation::class,
        mappedBy: 'attribute',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    public Collection $translations;

    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    public int $version;

    #[ORM\Column(type: UlidType::NAME)]
    public string $createdBy;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $updatedBy = null;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }
}
