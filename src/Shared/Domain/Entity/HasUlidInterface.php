<?php

declare(strict_types=1);

namespace App\Shared\Domain\Entity;

use App\Shared\Domain\ValueObject\Identity\Ulid;

interface HasUlidInterface
{
    public function getUlid(): Ulid;
}
