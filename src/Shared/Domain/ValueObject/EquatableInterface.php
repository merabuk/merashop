<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

interface EquatableInterface
{
    public function equals(object $other): bool;
}
