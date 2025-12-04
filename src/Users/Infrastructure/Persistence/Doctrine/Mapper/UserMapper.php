<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Mapper;

use App\Users\Domain\Entity\User;
use App\Users\Domain\ValueObject\EmailAddress;
use App\Users\Infrastructure\Persistence\Doctrine\Entity\OrmUser;

class UserMapper
{
    public const DOMAIN_CLASS_NAME = User::class;
    public const DOCTRINE_CLASS_NAME = OrmUser::class;

    public function toDoctrine(User $user): OrmUser
    {
        $ormUser = new OrmUser();

        $ormUser->setId($user->getId());
        $ormUser->setFirstName($user->getFirstName());
        $ormUser->setLastName($user->getLastName());
        $ormUser->setEmail($user->getEmail()->toString());
        $ormUser->setPhoneNumber($user->getPhoneNumber());
        $ormUser->setPassword($user->getPassword());

        return $ormUser;
    }

    public function fromDoctrine(OrmUser $ormUser): User
    {
        return new User(
            id: $ormUser->getId(),
            email: EmailAddress::fromString($ormUser->getEmail()),
            firstName: $ormUser->getFirstName(),
            lastName: $ormUser->getLastName(),
            phoneNumber: $ormUser->getPhoneNumber(),
            password: $ormUser->getPassword()
        );
    }
}
