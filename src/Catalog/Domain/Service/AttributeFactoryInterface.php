<?php

namespace App\Catalog\Domain\Service;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;

interface AttributeFactoryInterface
{
    /**
     * @param array<string, array{name: string}> $translations
     */
    public function createForTest(
        string $ulid,
        string $code,
        TypeEnum $type,
        array $translations,
        string $adminUlid,
    ): Attribute;
}
