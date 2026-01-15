<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

final readonly class GrantResultData
{
    /**
     * @param array<string> $roles
     * @param array<string> $scopes
     */
    public function __construct(
        public string $subjectUlid,
        public array $roles,
        public array $scopes = [],
        public string $type,
        public int $expiresIn,
        public bool $allowRefreshToken = false,
    ) {
    }
}
