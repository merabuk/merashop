<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Provider\Loader;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountUlidException;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\UserAccount\Ulid;
use App\IdentityAccess\Infrastructure\Security\Provider\AuthSubject;
use App\Shared\Domain\Enum\IdentityTypeEnum;

final readonly class UserAuthSubjectLoader implements AuthSubjectLoaderInterface
{
    public function __construct(
        private UserAccountReadRepositoryInterface $readRepository,
    ) {
    }

    public function load(string $ulid): ?AuthSubject
    {
        try {
            $user = $this->readRepository->findByUlid(Ulid::fromString($ulid));

            return $user ? AuthSubject::fromUserAccount($user) : null;
        } catch (InvalidUserAccountUlidException) {
            return null;
        }
    }

    public static function getDefaultIndexName(): string
    {
        return IdentityTypeEnum::User->value;
    }
}
