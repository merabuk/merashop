<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;

final readonly class AttributeFixture
{
    public function __construct(
        private AttributeMother $mother,
        private AttributeWriteRepositoryInterface $repository,
    ) {
    }

    public function create(array $overrides = []): Attribute
    {
        $attribute = $this->mother->create($overrides);

        return $this->repository->save($attribute);
    }

    /**
     * @return Attribute[]
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
