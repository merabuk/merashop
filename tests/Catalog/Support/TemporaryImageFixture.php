<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\TemporaryImage;
use App\Catalog\Domain\Enum\TemporaryImage\ContextEnum;
use App\Catalog\Domain\Repository\TemporaryImageWriteRepositoryInterface;

final readonly class TemporaryImageFixture
{
    public function __construct(
        private TemporaryImageMother $mother,
        private TemporaryImageWriteRepositoryInterface $repository,
    ) {
    }

    public function create(
        ?string $ulid = null,
        ?string $path = null,
        ?ContextEnum $context = null,
    ): TemporaryImage {
        $temporaryImage = $this->mother->create(
            ulid: $ulid,
            path: $path,
            context: $context,
        );

        return $this->repository->save($temporaryImage);
    }

    /**
     * @return TemporaryImage[]
     */
    public function createMany(int $count): array
    {
        $items = [];
        for ($i = 0; $i < $count; ++$i) {
            $items[] = $this->create();
        }

        return $items;
    }
}
