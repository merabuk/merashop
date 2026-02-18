<?php

declare(strict_types=1);

namespace App\Catalog\Application\Query\GetAttributeList;

use App\Shared\Application\Query\QueryInterface;
use App\Shared\Domain\Criteria\Listing\Criteria;

final readonly class GetAttributeListQuery implements QueryInterface
{
    public function __construct(
        public Criteria $criteria,
    ) {
    }
}
