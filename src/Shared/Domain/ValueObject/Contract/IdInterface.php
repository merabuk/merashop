<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Contract;

use Stringable;

interface IdInterface extends Stringable
{
    public function value(): int;
}
