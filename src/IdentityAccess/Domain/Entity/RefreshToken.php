<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Entity;

use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountType;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\AccountUlid;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\ExpiresAt;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\Id;
use App\IdentityAccess\Domain\ValueObject\RefreshToken\TokenHash;

readonly class RefreshToken
{
    public function __construct(
        private TokenHash $tokenHash,
        private AccountUlid $accountUlid,
        private AccountType $accountType,
        private ExpiresAt $expiresAt,
        private ?Id $id = null,
    ) {
    }

    public static function create(
        TokenHash $token,
        AccountUlid $accountUlid,
        AccountType $accountType,
        ExpiresAt $expiresAt,
    ): self {
        return new self(
            tokenHash: $token,
            accountUlid: $accountUlid,
            accountType: $accountType,
            expiresAt: $expiresAt
        );
    }

    public function getTokenHash(): TokenHash
    {
        return $this->tokenHash;
    }

    public function getAccountUlid(): AccountUlid
    {
        return $this->accountUlid;
    }

    public function getAccountType(): AccountType
    {
        return $this->accountType;
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
