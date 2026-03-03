<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Support;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;
use App\Catalog\Domain\Repository\AttributeWriteRepositoryInterface;

final readonly class AttributeFixture
{
    public function __construct(
        private AttributeMother $mother,
        private AttributeWriteRepositoryInterface $repository,
    ) {
    }

    /**
     * @param ?array<string, array{name: string}> $translations
     */
    public function create(
        ?string $ulid = null,
        ?string $code = null,
        ?TypeEnum $type = null,
        ?array $translations = null,
        ?string $createdByUlid = null,
    ): Attribute {
        $attribute = $this->mother->create(
            ulid: $ulid,
            code: $code,
            type: $type,
            translations: $translations,
            createdByUlid: $createdByUlid,
        );

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
