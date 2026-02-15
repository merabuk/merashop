<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\AccessTokenData;
use App\IdentityAccess\Application\DTO\GrantResultData;
use App\IdentityAccess\Application\DTO\RefreshTokenData;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\DTO\UserCredentialsInterface;
use App\IdentityAccess\Application\Exceptions\GrantHandlerException;
use App\IdentityAccess\Application\Exceptions\InvalidCredentialsException;
use App\IdentityAccess\Application\Exceptions\RefreshToken\CreateRefreshTokenException;
use App\IdentityAccess\Application\Exceptions\TokenGenerateException;
use App\IdentityAccess\Application\Exceptions\UnsupportedAccountProviderException;
use App\IdentityAccess\Application\Security\Provider\PasswordGrant\PasswordGrantAccountProviderInterface;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Application\Service\RefreshTokenService;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

readonly class PasswordGrantHandler implements GrantHandlerInterface
{
    public function __construct(
        #[AutowireLocator(
            services: 'identity_access.account_provider.password_grant',
            defaultIndexMethod: 'getDefaultIndexName',
        )]
        private ContainerInterface $providers,
        private TokenGeneratorInterface $tokenGenerator,
        private RefreshTokenService $refreshTokenService,
    ) {
    }

    public static function getDefaultIndexName(): string
    {
        return GrantTypeEnum::Password->value;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws GrantHandlerException
     * @throws NotFoundExceptionInterface
     * @throws UnsupportedAccountProviderException
     */
    public function handle(UserCredentialsInterface $data): TokenResponseData
    {
        try {
            $providerId = $data->getAccountType()->value;

            if (!$this->providers->has($providerId)) {
                throw new UnsupportedAccountProviderException(sprintf("Container does not have '%s' account provider for '%s' grant type handler", $providerId, self::getDefaultIndexName()));
            }

            $provider = $this->providers->get($providerId);

            if ($provider instanceof PasswordGrantAccountProviderInterface) {
                $grandData = $provider->handle($data->getUsername(), $data->getPassword());

                return new TokenResponseData(
                    accessTokenData: $this->getAccessTokenData($grandData),
                    refreshTokenData: $this->getRefreshTokenData($grandData),
                );
            }

            throw new UnsupportedAccountProviderException(sprintf('Account provider %s is not an instance of %s', is_object($provider) ? get_class($provider) : (string) $provider, PasswordGrantAccountProviderInterface::class));
        } catch (CreateRefreshTokenException|TokenGenerateException $e) {
            throw new InvalidCredentialsException('Failed to process user credentials', previous: $e);
        }
    }

    /**
     * @throws TokenGenerateException
     */
    private function getAccessTokenData(GrantResultData $grantResult): AccessTokenData
    {
        return $this->tokenGenerator->generateAccessToken($grantResult);
    }

    /**
     * @throws CreateRefreshTokenException
     */
    private function getRefreshTokenData(GrantResultData $grantResult): RefreshTokenData
    {
        return $this->refreshTokenService->create($grantResult->subjectUlid, $grantResult->subjectType);
    }
}
