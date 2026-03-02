<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

use App\IdentityAccess\Application\DTO\Contracts\ClientCredentialsInterface;
use App\IdentityAccess\Application\DTO\Contracts\RefreshTokenCredentialsInterface;
use App\IdentityAccess\Application\DTO\Contracts\UserCredentialsInterface;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use InvalidArgumentException;

readonly class OAuth2Data implements UserCredentialsInterface, ClientCredentialsInterface, RefreshTokenCredentialsInterface
{
    public function __construct(
        private string $grantType,
        private IdentityTypeEnum $accountType,
        private ?string $username = null,
        private ?string $password = null,
        private ?string $clientId = null,
        private ?string $clientSecret = null,
        private ?string $refreshToken = null,
    ) {
    }

    public function getGrantType(): GrantTypeEnum
    {
        return GrantTypeEnum::from($this->grantType);
    }

    public function getUsername(): string
    {
        return $this->username ?? throw $this->prepareException('username', GrantTypeEnum::Password);
    }

    public function getPassword(): string
    {
        return $this->password ?? throw $this->prepareException('password', GrantTypeEnum::Password);
    }

    public function getAccountType(): IdentityTypeEnum
    {
        return $this->accountType;
    }

    public function getClientId(): string
    {
        return $this->clientId ?? throw $this->prepareException('clientId', GrantTypeEnum::ClientCredentials);
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret ?? throw $this->prepareException('clientSecret', GrantTypeEnum::ClientCredentials);
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken ?? throw $this->prepareException('refreshToken', GrantTypeEnum::RefreshToken);
    }

    private function prepareException(string $field, GrantTypeEnum $grantTypeEnum): InvalidArgumentException
    {
        return new InvalidArgumentException(sprintf(
            "%s is required for '%s' grant",
            $field,
            $grantTypeEnum->value
        ));
    }
}
