<?php

namespace App\Catalog\Domain\Factory\Contract;

use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\AttributeOption;
use App\Catalog\Domain\Enum\Attribute\TypeEnum;

interface AttributeFactoryInterface
{
    /**
     * @param array<string, array{name: string}> $translations
     * @param AttributeOption[]                  $options
     */
    public function createForTest(
        string $ulid,
        string $code,
        TypeEnum $type,
        array $translations,
        string $createdByUlid,
        array $options,
    ): Attribute;
}
