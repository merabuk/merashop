<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Domain\Service\TokenHasherInterface;

readonly class TokenHasherService implements TokenHasherInterface
{
    public function __construct(
    ) {
    }

    public function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
