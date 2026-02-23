<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Service;

use App\IdentityAccess\Domain\Entity\AdminAccount;
use App\IdentityAccess\Domain\Enum\AdminAccount\StatusEnum;
use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountEmailException;
use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountPasswordHashException;
use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountUlidException;
use App\IdentityAccess\Domain\Service\AdminAccountFactoryInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\PasswordHash;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Status;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\IdentityAccess\Domain\ValueObject\RoleCollection;

final readonly class AdminAccountFactory implements AdminAccountFactoryInterface
{
    /**
     * @param string[] $roles
     *
     * @throws InvalidAdminAccountUlidException
     * @throws InvalidAdminAccountEmailException
     * @throws InvalidAdminAccountPasswordHashException
     */
    public function createForTest(
        string $ulid,
        string $email,
        string $passwordHash,
        array $roles,
        StatusEnum $status,
    ): AdminAccount {
        return AdminAccount::create(
            ulid: Ulid::fromString($ulid),
            email: EmailAddress::fromString($email),
            passwordHash: PasswordHash::fromString($passwordHash),
            roles: RoleCollection::fromStrings($roles),
            status: Status::fromEnum($status)
        );
    }
}
