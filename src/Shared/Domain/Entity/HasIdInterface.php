<?php

declare(strict_types=1);

namespace App\Shared\Domain\Entity;

use App\Shared\Domain\ValueObject\Contract\IdInterface;

interface HasIdInterface
{
    public function getId(): ?IdInterface;
}
