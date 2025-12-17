<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Repository;

use App\EmailSender\Domain\Entity\OutgoingEmail;
use App\EmailSender\Domain\Repository\OutgoingEmailWriteRepositoryInterface;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ThrowableValueObjectException;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;

class OutgoingEmailWriteRepository extends BaseOutgoingEmailRepository implements OutgoingEmailWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ThrowableValueObjectException
     */
    public function save(OutgoingEmail $outgoingEmail): OutgoingEmail
    {
        return $this->_save(domain: $outgoingEmail, id: $outgoingEmail->getId()?->value());
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function delete(OutgoingEmail $outgoingEmail): void
    {
        $this->_delete($outgoingEmail);
    }
}
