<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Contract;

interface IdInterface
{
    public function value(): int;
}
