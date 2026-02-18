<?php

declare(strict_types=1);

namespace App\Shared\Domain\Criteria\Sorting;

final readonly class Sort
{
    public const string ASC = 'ASC';
    public const string DESC = 'DESC';

    public function __construct(
        public string $field,
        public string $direction = self::ASC,
    ) {
    }
}
