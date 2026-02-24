<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Service;

use App\IdentityAccess\Domain\Entity\UserAccount;
use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountEmailException;
use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountPasswordHashException;
use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountUlidException;
use App\IdentityAccess\Domain\Service\UserAccountFactoryInterface;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\UserAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Ulid;

final readonly class UserAccountFactory implements UserAccountFactoryInterface
{
    /**
     * @param string[] $roles
     *
     * @throws InvalidUserAccountEmailException
     * @throws InvalidUserAccountPasswordHashException
     * @throws InvalidUserAccountUlidException
     */
    public function createForTest(
        string $ulid,
        string $email,
        string $passwordHash,
        array $roles,
    ): UserAccount {
        return UserAccount::create(
            ulid: Ulid::fromString($ulid),
            email: EmailAddress::fromString($email),
            passwordHash: PasswordHash::fromString($passwordHash),
            roles: RoleCollection::fromStrings($roles),
        );
    }
}
