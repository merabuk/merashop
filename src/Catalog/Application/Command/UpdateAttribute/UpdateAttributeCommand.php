<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateAttribute;

use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use App\Shared\Application\Command\CommandInterface;

final readonly class UpdateAttributeCommand implements CommandInterface
{
    /**
     * @param AttributeTranslationData[] $translations
     * @param AttributeOptionData[]      $options
     */
    public function __construct(
        public string $ulid,
        public string $code,
        public string $type,
        public array $translations,
        public array $options,
        public int $version,
        public string $adminUlid,
    ) {
    }
}
