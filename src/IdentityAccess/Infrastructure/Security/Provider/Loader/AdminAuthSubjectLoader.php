<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Provider\Loader;

use App\IdentityAccess\Domain\Exception\AdminAccount\InvalidAdminAccountUlidException;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\Ulid;
use App\IdentityAccess\Infrastructure\Security\Provider\AuthSubject;
use App\Shared\Domain\Enum\IdentityTypeEnum;

final readonly class AdminAuthSubjectLoader implements AuthSubjectLoaderInterface
{
    public function __construct(
        private AdminAccountReadRepositoryInterface $readRepository,
    ) {
    }

    public function load(string $ulid): ?AuthSubject
    {
        try {
            $admin = $this->readRepository->findByUlid(Ulid::fromString($ulid));

            return $admin ? AuthSubject::fromAdminAccount($admin) : null;
        } catch (InvalidAdminAccountUlidException) {
            return null;
        }
    }

    public static function getDefaultIndexName(): string
    {
        return IdentityTypeEnum::Admin->value;
    }
}
