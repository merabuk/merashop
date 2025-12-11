<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Repository;

use App\Users\Domain\Entity\User;
use App\Users\Domain\Repository\UserReadRepositoryInterface;
use App\Users\Domain\ValueObject\EmailAddress;
use App\Users\Infrastructure\Persistence\Doctrine\Entity\OrmUser;

class UserReadRepository extends BaseUserRepository implements UserReadRepositoryInterface
{
    public function findById(int $id): ?User
    {
        $ormUser = $this->find($id);

        return $this->checkAndMapToDomain($ormUser);
    }

    public function findByEmail(EmailAddress $email): ?User
    {
        $ormUser = $this->findOneBy(['email' => $email->value()]);

        return $this->checkAndMapToDomain($ormUser);
    }

    private function checkAndMapToDomain(?object $ormUser): ?User
    {
        if (false === $ormUser instanceof OrmUser) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($ormUser);
    }
}
