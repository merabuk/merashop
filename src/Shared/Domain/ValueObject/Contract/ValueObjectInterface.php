<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject\Contract;

use Stringable;

interface ValueObjectInterface extends EquatableInterface, Stringable
{
}
