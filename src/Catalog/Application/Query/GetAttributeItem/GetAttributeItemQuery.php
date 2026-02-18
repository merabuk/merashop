<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query\GetAttributeItem;

use App\Shared\Application\Query\QueryInterface;

final readonly class GetAttributeItemQuery implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {
    }
}
