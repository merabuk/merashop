<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Repository;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutboxEmail;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;

class OutboxEmailReadRepository extends BaseOutgoingEmailRepository implements OutboxEmailReadRepositoryInterface
{
    /**
     * @throws InvalidEmailSenderValueObjectException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     */
    public function findById(int $id): ?OutboxEmail
    {
        $ormOutgoingEmail = $this->find($id);

        return $this->checkAndMapToDomain($ormOutgoingEmail);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidEmailSenderValueObjectException
     */
    private function checkAndMapToDomain(?object $ormOutgoingEmail): ?OutboxEmail
    {
        if (false === $ormOutgoingEmail instanceof OrmOutboxEmail) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($ormOutgoingEmail);
    }
}
