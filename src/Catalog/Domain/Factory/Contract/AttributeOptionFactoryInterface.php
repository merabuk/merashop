<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Factory\Contract;

use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\AttributeOptionMetadataInterface;

interface AttributeOptionFactoryInterface
{
    /**
     * @param array<string, array{value: string}> $translations
     */
    public function createForTest(
        string $ulid,
        string $code,
        array $translations,
        bool $isActive,
        string $createdByUlid,
        ?AttributeOptionMetadataInterface $metadata = null,
    ): AttributeOption;
}
