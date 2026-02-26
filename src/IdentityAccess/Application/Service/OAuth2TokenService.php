<?php

declare(strict_types=1);

namespace App\IdentityAccess\Application\Service;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Application\DTO\TokenResponseData;
use App\IdentityAccess\Application\Exception\GrantHandlerException;
use App\IdentityAccess\Application\Exception\UnsupportedGrantTypeException;
use App\IdentityAccess\Application\Security\Grant\GrantHandlerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

final readonly class OAuth2TokenService implements OAuth2TokenServiceInterface
{
    public function __construct(
        #[AutowireLocator(
            services: 'identity_access.grant_handler',
            defaultIndexMethod: 'getDefaultIndexName',
        )]
        private ContainerInterface $handlers,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws GrantHandlerException
     * @throws NotFoundExceptionInterface
     * @throws UnsupportedGrantTypeException
     */
    public function handle(OAuth2Data $data): TokenResponseData
    {
        $id = $data->getGrantType()->value;

        if (!$this->handlers->has($id)) {
            throw new UnsupportedGrantTypeException(sprintf("Container does not have a handler for '%s' grant type", $id));
        }

        $handler = $this->handlers->get($id);

        if ($handler instanceof GrantHandlerInterface) {
            return $handler->handle($data);
        }

        throw new UnsupportedGrantTypeException(sprintf('Grant type handler %s is not an instance of %s', get_debug_type($handler), GrantHandlerInterface::class));
    }
}
