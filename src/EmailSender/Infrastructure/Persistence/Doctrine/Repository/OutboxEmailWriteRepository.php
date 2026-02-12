<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Repository;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Repository\OutboxEmailWriteRepositoryInterface;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObjectExceptionInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\WriteRepositoryTrait;
use Doctrine\ORM\Exception\ORMException;

class OutboxEmailWriteRepository extends BaseOutgoingEmailRepository implements OutboxEmailWriteRepositoryInterface
{
    use WriteRepositoryTrait;

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws ORMException
     * @throws ValueObjectExceptionInterface
     */
    public function save(OutboxEmail $outgoingEmail): OutboxEmail
    {
        $orm = $this->_save(domain: $outgoingEmail, id: $outgoingEmail->getId()?->value());

        return $this->mapper->fromDoctrineOrm($orm);
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function delete(OutboxEmail $outgoingEmail): void
    {
        $this->_delete($outgoingEmail);
    }
}
