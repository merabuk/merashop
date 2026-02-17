<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query\GetAttribute;

use App\Shared\Application\Query\QueryInterface;

final readonly class GetAttributeQuery implements QueryInterface
{
    public function __construct(
        public int $id,
    ) {
    }
}
