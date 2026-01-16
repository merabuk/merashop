<?php

namespace App\IdentityAccess\Presentation\ApiVersion1\Resource;

use App\IdentityAccess\Application\DTO\TokenResponseData;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TokenNormalizer implements NormalizerInterface
{
    /**
     * @param TokenResponseData $data
     * @param array<string, mixed> $context
     * @return array<string, int|string>
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        // RFC 6749
        $response = [
            'access_token' => $data->accessTokenData->token,
            'token_type' => 'Bearer',
            'expires_in' => $data->accessTokenData->expiresIn,
        ];

        if (null !== $data->refreshTokenData) {
            $response['refresh_token'] = $data->refreshTokenData->token;
        }

        return $response;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TokenResponseData;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [TokenResponseData::class => true];
    }
}
