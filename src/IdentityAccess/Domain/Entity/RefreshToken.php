<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Entity;

use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Id;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Token;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Ulid;

class RefreshToken
{
    public function __construct(
        private Token $token,
        private Ulid $accountUlid,
        private ExpiresAt $expiresAt,
        private readonly ?Id $id = null,
    ) {
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function getAccountUlid(): Ulid
    {
        return $this->accountUlid;
    }

    public function getExpiresAt(): ExpiresAt
    {
        return $this->expiresAt;
    }

    public function getId(): ?Id
    {
        return $this->id;
    }
}
