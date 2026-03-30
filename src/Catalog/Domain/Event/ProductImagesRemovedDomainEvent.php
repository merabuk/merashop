<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Event;

use App\Catalog\Domain\Enum\CatalogEventNameEnum;
use App\Shared\Domain\Bus\AsyncMessageInterface;

readonly class ProductImagesRemovedDomainEvent implements AsyncMessageInterface
{
    public function __construct(
        /**
         * @var string[]
         */
        public array $productImagePaths,
    ) {
    }

    public function getRoutingKey(): string
    {
        return CatalogEventNameEnum::ProductImagesRemoved->value;
    }
}
