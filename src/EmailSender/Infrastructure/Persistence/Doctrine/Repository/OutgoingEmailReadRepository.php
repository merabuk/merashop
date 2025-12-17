<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Repository;

use App\EmailSender\Domain\Entity\OutgoingEmail;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\Repository\OutgoingEmailReadRepositoryInterface;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutgoingEmail;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;

class OutgoingEmailReadRepository extends BaseOutgoingEmailRepository implements OutgoingEmailReadRepositoryInterface
{
    /**
     * @throws InvalidEmailSenderValueObjectException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     */
    public function findById(int $id): ?OutgoingEmail
    {
        $ormOutgoingEmail = $this->find($id);

        return $this->checkAndMapToDomain($ormOutgoingEmail);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidEmailSenderValueObjectException
     */
    private function checkAndMapToDomain(?object $ormOutgoingEmail): ?OutgoingEmail
    {
        if (false === $ormOutgoingEmail instanceof OrmOutgoingEmail) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($ormOutgoingEmail);
    }
}
