<?php

declare(strict_types=1);

namespace App\Catalog\Application\DTO\Attribute;

final readonly class AttributeOptionData
{
    public function __construct(
        public string $code,
        /**
         * @var AttributeOptionTranslationData[]
         */
        public array $translations,
        public bool $isActive,
        public ?float $baseRatio = null,
    ) {
    }
}
