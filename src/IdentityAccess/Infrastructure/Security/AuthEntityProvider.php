<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountUlidException;
use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountUlidException;
use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountUlidException;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid as AdminAccountUlid;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid as ModuleAccountUlid;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Ulid as UserAccountUlid;
use App\IdentityAccess\Infrastructure\Exception\InvalidAuthEntityException;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Throwable;

/**
 * @implements UserProviderInterface<AuthSubject>
 */
final readonly class AuthEntityProvider implements UserProviderInterface
{
    public const string SEPARATOR = ':';

    public function __construct(
        private UserAccountReadRepositoryInterface $userAccountReadRepository,
        private ModuleAccountReadRepositoryInterface $moduleAccountReadRepository,
        private AdminAccountReadRepositoryInterface $adminAccountReadRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws InvalidAuthEntityException
     */
    public function loadUserByIdentifier(string $identifier): AuthSubject
    {
        if (!str_contains($identifier, self::SEPARATOR)) {
            return $this->fallbackLoad($identifier);
        }

        [$type, $ulidString] = explode(self::SEPARATOR, $identifier, 2);

        try {
            $authSubject = match (IdentityTypeEnum::tryFrom($type)) {
                IdentityTypeEnum::User => $this->processUserAccount($ulidString),
                IdentityTypeEnum::Module => $this->processModuleAccount($ulidString),
                IdentityTypeEnum::Admin => $this->processAdminAccount($ulidString),
                default => throw new RuntimeException(sprintf('Invalid auth entity type: %s', $type)),
            };

            if ($authSubject) {
                return $authSubject;
            }
        } catch (Throwable $e) {
            $this->logger->error('Error to authenticate entity from identifier', [
                'error_message' => $e->getMessage(),
                'identifier' => $identifier,
                'type' => $type,
            ]);
        }

        throw new InvalidAuthEntityException(message: sprintf('Entity with type "%s" and ID "%s" not found.', $type, $ulidString), previous: $e ?? null);
    }

    /**
     * @throws InvalidAuthEntityException
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

    /**
     * @throws InvalidUserAccountUlidException
     */
    private function processUserAccount(string $ulidString): ?AuthSubject
    {
        $user = $this->userAccountReadRepository->findByUlid(UserAccountUlid::fromString($ulidString));

        return $user ? AuthSubject::fromUserAccount($user) : null;
    }

    /**
     * @throws InvalidModuleAccountUlidException
     */
    private function processModuleAccount(string $ulidString): ?AuthSubject
    {
        $module = $this->moduleAccountReadRepository->findByUlid(ModuleAccountUlid::fromString($ulidString));

        return $module ? AuthSubject::fromModuleAccount($module) : null;
    }

    /**
     * @throws InvalidAdminAccountUlidException
     */
    private function processAdminAccount(string $ulidString): ?AuthSubject
    {
        $admin = $this->adminAccountReadRepository->findByUlid(AdminAccountUlid::fromString($ulidString));

        return $admin ? AuthSubject::fromAdminAccount($admin) : null;
    }

    private function fallbackLoad(string $identifier): AuthSubject
    {
        throw new RuntimeException(sprintf("Given identifier '%s' does not contain a type prefix", $identifier));
    }
}
