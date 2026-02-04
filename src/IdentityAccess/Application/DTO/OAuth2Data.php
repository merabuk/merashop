<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\DTO;

use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use InvalidArgumentException;

readonly class OAuth2Data implements UserCredentialsInterface, ClientCredentialsInterface, RefreshTokenInterface
{
    public function __construct(
        private string $grantType,
        private ?string $username,
        private ?string $password,
        private ?string $clientId,
        private ?string $clientSecret,
        private ?string $refreshToken,
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
