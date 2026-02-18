<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Criteria\Restrictions;

final readonly class Criterion
{
    public function __construct(
        public string $field,
        public mixed $value,
        public mixed $type = null,
    ) {
    }
}
