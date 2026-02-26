<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Security\Grant;

use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\DTO\UserCredentialsInterface;
use App\IdentityAccess\Application\Exception\GrantHandlerException;
use App\IdentityAccess\Application\Exception\InvalidCredentialsException;
use App\IdentityAccess\Application\Exception\RefreshToken\CreateRefreshTokenException;
use App\IdentityAccess\Application\Exception\TokenGenerateException;
use App\IdentityAccess\Application\Exception\UnsupportedAccountProviderException;
use App\IdentityAccess\Application\Security\Provider\PasswordGrant\PasswordGrantAccountProviderInterface;
use App\IdentityAccess\Application\Security\TokenGeneratorInterface;
use App\IdentityAccess\Application\Service\RefreshTokenServiceInterface;
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
        private RefreshTokenServiceInterface $refreshTokenService,
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
                $grandData = $provider->handle(
                    username: $data->getUsername(),
                    password: $data->getPassword()
                );

                return new TokenResponseData(
                    accessTokenData: $this->tokenGenerator->generateAccessToken($grandData),
                    refreshTokenData: $this->refreshTokenService->create($grandData->subjectUlid, $grandData->subjectType),
                );
            }

            throw new UnsupportedAccountProviderException(sprintf('Account provider %s is not an instance of %s', get_debug_type($provider), PasswordGrantAccountProviderInterface::class));
        } catch (CreateRefreshTokenException|TokenGenerateException $e) {
            throw new InvalidCredentialsException('Failed to process user credentials', previous: $e);
        }
    }
}
