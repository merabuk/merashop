<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Catalog\Domain\ValueObject\Category\Path;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Infrastructure\Persistence\Doctrine\Type\Category\StatusType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Schema\ForeignKeyConstraint\ReferentialAction;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'categories')]
#[ORM\UniqueConstraint(name: 'uniq_categories_ulid', columns: ['ulid'])]
#[ORM\UniqueConstraint(name: 'uniq_categories_slug', columns: ['slug'])]
#[ORM\Index(name: 'idx_categories_parent_id', columns: ['parent_id'])]
class OrmCategory
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME)]
    public string $ulid;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(
        name: 'parent_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: ReferentialAction::SET_NULL->value
    )]
    public ?self $parent = null;

    #[ORM\Column(type: Types::STRING, length: Path::MAX_LENGTH)]
    public string $path;

    #[ORM\Column(type: Types::STRING, length: Slug::MAX_LENGTH)]
    public string $slug;

    #[ORM\Column(type: Types::INTEGER)]
    public int $sortOrder = 0;

    #[ORM\Column(type: StatusType::NAME)]
    public StatusEnum $status = StatusEnum::Active;

    /**
     * @var Collection<int, OrmCategoryTranslation>
     */
    #[ORM\OneToMany(
        targetEntity: OrmCategoryTranslation::class,
        mappedBy: 'category',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    public Collection $translations;

    /**
     * @var Collection<int, OrmProduct>
     */
    #[ORM\ManyToMany(targetEntity: OrmProduct::class, mappedBy: 'categories')]
    public Collection $products;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->products = new ArrayCollection();
    }
}
