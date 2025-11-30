<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Mapper;

use App\Users\Domain\Entity\User;
use App\Users\Infrastructure\Persistence\Doctrine\Entity\OrmUser;

class UserMapper
{
    const DOMAIN_CLASS_NAME = User::class;
    const DOCTRINE_CLASS_NAME = OrmUser::class;

    public function toDoctrine(User $user): OrmUser
    {
        $ormUser = new OrmUser();

        $ormUser->setId($user->getId());
        $ormUser->setFirstName($user->getFirstName());
        $ormUser->setLastName($user->getLastName());
        $ormUser->setEmail($user->getEmail());
        $ormUser->setPhoneNumber($user->getPhoneNumber());
        $ormUser->setPassword($user->getPassword());
        $ormUser->setCreatedAt();
        $ormUser->setUpdatedAt();
        $ormUser->setDeletedAt();

        return $ormUser;
    }

    public function fromDoctrine(OrmUser $ormUser): User
    {
        $user = new User();

        $user->setId($ormUser->getId());
        $user->setFirstName($ormUser->getFirstName());
        $user->setLastName($ormUser->getLastName());
        $user->setEmail($ormUser->getEmail());
        $user->setPhoneNumber($ormUser->getPhoneNumber());
        $user->setPassword($ormUser->getPassword());
        // mapping another properties

        return $user;
    }
}
