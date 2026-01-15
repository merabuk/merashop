<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Service;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exceptions\BadCredentialsException;
use App\IdentityAccess\Application\Security\Grant\GrantHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class OAuth2TokenService
{
    /**
     * @param iterable<GrantHandlerInterface> $handlers
     */
    public function __construct(
        #[AutowireIterator('identity_access.grant_handler')]
        private iterable $handlers,
    ) {
    }

    /**
     * @throws BadCredentialsException
     */
    public function handle(OAuth2Data $data): TokenResponseData
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($data->getGrantType())) {
                return $handler->handle($data);
            }
        }

        throw BadCredentialsException::becauseUnsupportedGrantType();
    }
}
