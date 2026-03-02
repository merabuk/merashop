<?php

declare(strict_types=1);

namespace App\IdentityAccess\Presentation\Http\Common\Resource;

use App\IdentityAccess\Application\DTO\TokenResponseData;

trait AccessTokenResourceTrait
{
    private function __construct(
        public readonly string $access_token,
        public readonly int $expires_in,
        public readonly string $token_type = 'Bearer',
        public readonly ?string $refresh_token = null,
    ) {
    }

    public static function fromDto(TokenResponseData $data): self
    {
        return new self(
            access_token: $data->accessTokenData->token,
            expires_in: $data->accessTokenData->expiresIn,
            refresh_token: $data->refreshTokenData?->token
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        // RFC 6749
        return array_filter([
            'access_token' => $this->access_token,
            'token_type' => $this->token_type,
            'expires_in' => $this->expires_in,
            'refresh_token' => $this->refresh_token,
        ], static fn ($value) => null !== $value);
    }
}
