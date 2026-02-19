<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class MapPagination
{
    /**
     * @param string[] $allowedSortFields
     */
    public function __construct(
        public array $allowedSortFields = [],
    ) {
    }
}
