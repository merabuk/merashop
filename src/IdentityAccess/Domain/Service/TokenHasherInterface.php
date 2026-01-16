<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Service;

interface TokenHasherInterface
{
    public function hash(string $plainToken): string;
}
