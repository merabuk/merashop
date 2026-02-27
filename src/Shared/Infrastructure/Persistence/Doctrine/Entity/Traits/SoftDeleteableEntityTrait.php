<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Entity\Traits;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * Modernized Soft Deleteable Entity trait.
 *
 * @see SoftDeleteableEntity
 */
trait SoftDeleteableEntityTrait
{
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public private(set) ?DateTimeImmutable $deletedAt = null;

    public function setDeletedAt(DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }
}
