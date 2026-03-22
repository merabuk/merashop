<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\CreateAttribute;

use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use App\Shared\Application\Command\CommandInterface;

final readonly class CreateAttributeCommand implements CommandInterface
{
    public function __construct(
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
        public string $adminUlid,
    ) {
    }
}
