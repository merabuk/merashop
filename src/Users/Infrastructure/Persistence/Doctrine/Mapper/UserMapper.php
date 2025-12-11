<?php

declare(strict_types=1);

namespace App\Users\Infrastructure\Persistence\Doctrine\Mapper;

use App\Users\Domain\Entity\User;
use App\Users\Domain\Exception\InvalidUserValueObjectException;
use App\Users\Domain\ValueObject\EmailAddress;
use App\Users\Domain\ValueObject\FirstName;
use App\Users\Domain\ValueObject\LastName;
use App\Users\Domain\ValueObject\PasswordHash;
use App\Users\Domain\ValueObject\PhoneNumber;
use App\Users\Domain\ValueObject\UserId;
use App\Users\Infrastructure\Persistence\Doctrine\Entity\OrmUser;

class UserMapper
{
    public function toDoctrineOrm(User $user): OrmUser
    {
        $ormUser = new OrmUser();

        $ormUser->setId($user->getId()?->value());
        $ormUser->setFirstName($user->getFirstName()->value());
        $ormUser->setLastName($user->getLastName()->value());
        $ormUser->setEmail($user->getEmail()->value());
        $ormUser->setPhoneNumber($user->getPhoneNumber()?->value());
        $ormUser->setPassword($user->getPassword()->value());

        return $ormUser;
    }

    /**
     * @throws InvalidUserValueObjectException
     */
    public function fromDoctrineOrm(OrmUser $ormUser): User
    {
        return new User(
            id: UserId::fromInt($ormUser->getId() ?? throw new \RuntimeException('Missing user id')),
            email: EmailAddress::fromString($ormUser->getEmail()),
            firstName: FirstName::fromString($ormUser->getFirstName()),
            lastName: LastName::fromString($ormUser->getLastName()),
            phoneNumber: PhoneNumber::fromString($ormUser->getPhoneNumber()),
            password: PasswordHash::fromString($ormUser->getPassword()),
        );
    }
}
