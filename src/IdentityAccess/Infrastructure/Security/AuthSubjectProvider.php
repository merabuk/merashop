<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security;

use App\IdentityAccess\Domain\Repository\ModuleAccountRepositoryInterface;
use App\IdentityAccess\Domain\Repository\UserAccountRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\IdentityAccess\Domain\ValueObject\Ulid;
use App\Shared\Domain\Exception\InvalidEmailAddressException;
use App\Shared\Domain\Exception\InvalidUlidException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<AuthSubject>
 */
final readonly class AuthSubjectProvider implements UserProviderInterface
{
    public function __construct(
        private UserAccountRepositoryInterface $userAccountRepository,
        private ModuleAccountRepositoryInterface $moduleAccountRepository,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Пробуем найти по ULID (для AccessTokenHandler)
        try {
            $ulid = Ulid::fromString($identifier);

            $user = $this->userAccountRepository->findByUlid($ulid);
            if (null !== $user) {
                return AuthSubject::fromUserAccount($user);
            }

            $module = $this->moduleAccountRepository->findByUlid($ulid);
            if (null !== $module) {
                return AuthSubject::fromModuleAccount($module);
            }
        } catch (InvalidUlidException) {
            // Игнорируем, если это не ULID, и продолжаем поиск по другим идентификаторам
        }

        // Сначала пробуем найти как UserAccount (по email)
        try {
            $email = EmailAddress::fromString($identifier);
            $user = $this->userAccountRepository->findByEmail($email);
            if (null !== $user) {
                return AuthSubject::fromUserAccount($user);
            }
        } catch (InvalidEmailAddressException) {
            // Если это не валидный email, значит это точно не UserAccount
        }

        // Затем пробуем найти как ModuleAccount (по client_id)
        try {
            $clientId = ClientId::fromString($identifier);
            $module = $this->moduleAccountRepository->findByClientId($clientId);
            if (null !== $module) {
                return AuthSubject::fromModuleAccount($module);
            }
        } catch (\InvalidArgumentException) {
            // Если это не валидный client_id
        }

        throw new UserNotFoundException(sprintf('User or Module with identifier "%s" not found.', $identifier));
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof AuthSubject) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return AuthSubject::class === $class || is_subclass_of($class, AuthSubject::class);
    }
}
