<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Provider;

use App\IdentityAccess\Infrastructure\Exception\InvalidAuthEntityException;
use App\IdentityAccess\Infrastructure\Security\Provider\Loader\AuthSubjectLoaderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<AuthSubject>
 */
final readonly class AuthEntityProvider implements UserProviderInterface
{
    public const string SEPARATOR = ':';

    public function __construct(
        #[AutowireLocator(
            services: 'identity_access.auth_subject_loader',
            defaultIndexMethod: 'getDefaultIndexName',
        )]
        private ContainerInterface $loaders,
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidAuthEntityException
     * @throws NotFoundExceptionInterface
     */
    public function loadUserByIdentifier(string $identifier): AuthSubject
    {
        if (!str_contains($identifier, self::SEPARATOR)) {
            return $this->fallbackLoad($identifier);
        }

        [$type, $ulidString] = explode(self::SEPARATOR, $identifier, 2);

        if (!$this->loaders->has($type)) {
            throw new InvalidAuthEntityException(sprintf("Container does not have auth entity loader for '%s' type", $type));
        }

        $loader = $this->loaders->get($type);

        if ($loader instanceof AuthSubjectLoaderInterface) {
            return $loader->load($ulidString);
        }

        throw new InvalidAuthEntityException(message: sprintf('Entity with type "%s" and ID "%s" not found.', $type, $ulidString));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidAuthEntityException
     * @throws NotFoundExceptionInterface
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof AuthSubject) {
            throw new InvalidAuthEntityException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return AuthSubject::class === $class || is_subclass_of($class, AuthSubject::class);
    }

    private function fallbackLoad(string $identifier): AuthSubject
    {
        throw new RuntimeException(sprintf("Given identifier '%s' does not contain a type prefix", $identifier));
    }
}
