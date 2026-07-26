<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateAttribute;

use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use App\Shared\Application\Command\CommandInterface;

final readonly class CreateAttributeCommand implements CommandInterface
{
    /**
     * @param AttributeTranslationData[] $translations
     * @param AttributeOptionData[]      $options
     */
    public function __construct(
        public string $code,
        public string $type,
        public array $translations,
        public array $options,
        public string $adminUlid,
    ) {
    }
}
