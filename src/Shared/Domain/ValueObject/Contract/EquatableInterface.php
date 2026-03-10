<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Contract;

interface EquatableInterface
{
    public function equals(object $other): bool;
}
