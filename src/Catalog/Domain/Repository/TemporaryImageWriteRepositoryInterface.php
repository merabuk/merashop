<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;
use DateTimeImmutable;

interface TemporaryImageWriteRepositoryInterface
{
    public function save(TemporaryImage $temporaryImage): TemporaryImage;

    public function delete(TemporaryImage $temporaryImage): void;

    /**
     * @param Ulid[] $ulids
     */
    public function deleteByUlids(array $ulids): int;

    public function deleteOlderThan(DateTimeImmutable $date): int;
}
