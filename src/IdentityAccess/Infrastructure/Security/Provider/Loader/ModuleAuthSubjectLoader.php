<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Security\Provider\Loader;

use App\IdentityAccess\Domain\Exception\ModuleAccount\InvalidModuleAccountUlidException;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\Ulid;
use App\IdentityAccess\Infrastructure\Security\Provider\AuthSubject;
use App\Shared\Domain\Enum\IdentityTypeEnum;

final readonly class ModuleAuthSubjectLoader implements AuthSubjectLoaderInterface
{
    public function __construct(
        private ModuleAccountReadRepositoryInterface $readRepository,
    ) {
    }

    public function load(string $ulid): ?AuthSubject
    {
        try {
            $module = $this->readRepository->findByUlid(Ulid::fromString($ulid));

            return $module ? AuthSubject::fromModuleAccount($module) : null;
        } catch (InvalidModuleAccountUlidException) {
            return null;
        }
    }

    public static function getDefaultIndexName(): string
    {
        return IdentityTypeEnum::Module->value;
    }
}
