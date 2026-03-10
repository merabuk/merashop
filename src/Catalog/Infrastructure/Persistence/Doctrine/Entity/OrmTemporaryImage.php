<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Infrastructure\Persistence\Doctrine\Type\TemporaryImage\ContextType;
use App\Shared\Domain\ValueObject\RelativeFilePath;
use App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits\CreatedAtEntityTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UlidType;

#[ORM\Entity]
#[ORM\Table(name: 'temporary_images')]
#[ORM\UniqueConstraint(name: 'uniq_temporary_images_ulid', columns: ['ulid'])]
class OrmTemporaryImage
{
    use CreatedAtEntityTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['unsigned' => true])]
    public private(set) ?int $id = null;

    #[ORM\Column(type: UlidType::NAME)]
    public ?string $ulid = null;

    #[ORM\Column(type: Types::STRING, length: RelativeFilePath::MAX_LENGTH)]
    public ?string $path = null;

    #[ORM\Column(type: ContextType::NAME)]
    public ?ContextEnum $context = null;
}
