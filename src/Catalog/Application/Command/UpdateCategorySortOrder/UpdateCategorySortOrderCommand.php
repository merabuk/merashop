<?php

declare(strict_types=1);

namespace App\Catalog\Application\Command\UpdateCategorySortOrder;

use App\Shared\Application\Command\CommandInterface;

class UpdateCategorySortOrderCommand implements CommandInterface
{
    public function __construct(
        public int $id,
        public int $sortOrder,
        public int $version,
        public string $adminUlid,
    ) {
    }
}
