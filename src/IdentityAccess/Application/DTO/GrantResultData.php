<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

use App\Shared\Domain\Enum\IdentityTypeEnum;

final readonly class GrantResultData
{
    /**
     * @param non-empty-string $subjectUlid
     * @param array<string>    $roles
     * @param array<string>    $scopes
     */
    public function __construct(
        public string $subjectUlid,
        public IdentityTypeEnum $subjectType,
        public array $roles,
        public array $scopes = [],
        public bool $allowRefreshToken = false,
    ) {
    }
}
