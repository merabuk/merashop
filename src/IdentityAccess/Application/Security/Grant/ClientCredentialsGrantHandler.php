<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\Contracts\ClientCredentialsInterface;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exception\GrantHandlerException;
use App\IdentityAccess\Application\Exception\InvalidClientException;
use App\IdentityAccess\Application\Exception\TokenGenerateException;
use App\IdentityAccess\Application\Exception\UnsupportedAccountProviderException;
use App\IdentityAccess\Application\Security\Provider\ClientCredentialsGrant\ClientCredentialsGrantAccountProviderInterface;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final readonly class ClientCredentialsGrantHandler implements GrantHandlerInterface
{
    public function __construct(
        #[AutowireLocator(
            services: 'identity_access.account_provider.client_credentials_grant',
            defaultIndexMethod: 'getDefaultIndexName',
        )]
        private ContainerInterface $providers,
        private TokenGeneratorInterface $tokenGenerator,
    ) {
    }

    public static function getDefaultIndexName(): string
    {
        return GrantTypeEnum::ClientCredentials->value;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws GrantHandlerException
     * @throws NotFoundExceptionInterface
     * @throws UnsupportedAccountProviderException
     */
    public function handle(ClientCredentialsInterface $data): TokenResponseData
    {
        try {
            $providerId = $data->getAccountType()->value;

            if (!$this->providers->has($providerId)) {
                throw new UnsupportedAccountProviderException(sprintf("Container does not have '%s' account provider for '%s' grant type handler", $providerId, self::getDefaultIndexName()));
            }

            $provider = $this->providers->get($providerId);

            if ($provider instanceof ClientCredentialsGrantAccountProviderInterface) {
                $grandData = $provider->handle(
                    clientId: $data->getClientId(),
                    clientSecret: $data->getClientSecret()
                );

                return new TokenResponseData(accessTokenData: $this->tokenGenerator->generateAccessToken($grandData));
            }

            throw new UnsupportedAccountProviderException(sprintf('Account provider %s is not an instance of %s', get_debug_type($provider), ClientCredentialsGrantAccountProviderInterface::class));
        } catch (TokenGenerateException $e) {
            throw new InvalidClientException('Failed to process client credentials', previous: $e);
        }
    }
}
