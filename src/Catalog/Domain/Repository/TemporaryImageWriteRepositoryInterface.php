<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\TemporaryImage;
use DateTimeImmutable;

interface TemporaryImageWriteRepositoryInterface
{
    public function save(TemporaryImage $temporaryImage): TemporaryImage;

    public function delete(TemporaryImage $temporaryImage): void;

    public function deleteOlderThan(DateTimeImmutable $date): int;
}
