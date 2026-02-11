<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Stringable;

interface IdInterface extends Stringable
{
    public function value(): int;
}
