<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Repository;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Exception\TemporaryImage\OneOfTemporaryImagesNotFoundException;
use App\Catalog\Domain\ValueObject\TemporaryImage\Id;
use App\Catalog\Domain\ValueObject\TemporaryImage\Ulid;

interface TemporaryImageReadRepositoryInterface
{
    public function findById(Id $id): ?TemporaryImage;

    public function findByUlid(Ulid $ulid): ?TemporaryImage;

    /**
     * @param Ulid[] $ulids
     *
     * @return TemporaryImage[]
     */
    public function findByUlids(array $ulids): array;

    /**
     * @param Ulid[] $ulids
     *
     * @throws OneOfTemporaryImagesNotFoundException
     */
    public function assertAllExistByUlidsAndContext(array $ulids, ContextEnum $context): void;
}
