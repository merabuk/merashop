<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Event;

use App\Catalog\Domain\Enum\CatalogEventNameEnum;
use App\Shared\Domain\Bus\AsyncMessageInterface;

readonly class CategoryMovedDomainEvent implements AsyncMessageInterface
{
    public function __construct(
        public string $oldPath,
        public string $newPath,
    ) {
    }

    public function getRoutingKey(): string
    {
        return CatalogEventNameEnum::CategoryMoved->value;
    }
}
