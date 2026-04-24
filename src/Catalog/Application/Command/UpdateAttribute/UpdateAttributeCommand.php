<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateAttribute;

use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use App\Shared\Application\Command\CommandInterface;

final readonly class UpdateAttributeCommand implements CommandInterface
{
    public function __construct(
        public string $ulid,
        public string $code,
        public string $type,
        /**
         * @var AttributeTranslationData[]
         */
        public array $translations,
        /**
         * @var AttributeOptionData[]
         */
        public array $options,
        public int $version,
        public string $adminUlid,
    ) {
    }
}
