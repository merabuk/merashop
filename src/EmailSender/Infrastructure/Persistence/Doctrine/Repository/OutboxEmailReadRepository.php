<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Repository;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutboxEmail;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\InvalidTraceIdException;

class OutboxEmailReadRepository extends BaseOutgoingEmailRepository implements OutboxEmailReadRepositoryInterface
{
    /**
     * @throws InvalidEmailSenderValueObjectException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidTraceIdException
     */
    public function findById(int $id): ?OutboxEmail
    {
        $ormOutgoingEmail = $this->find($id);

        return $this->checkAndMapToDomain($ormOutgoingEmail);
    }

    /**
     * @return array<int, OutboxEmail>
     */
    public function findReadyToProcess(int $limit): array
    {
        $now = new \DateTimeImmutable();
        $staleTime = new \DateTimeImmutable('-10 minutes');

        /**
         * @var array<int, OrmOutboxEmail> $ormEmails
         */
        $ormEmails = $this->createQueryBuilder('e')
            ->where('e.status IN (:statuses)')
            ->andWhere('e.scheduledAt IS NULL OR e.scheduledAt <= :now')
            ->andWhere('e.lockedAt IS NULL OR e.lockedAt <= :staleTime')
            ->setParameter('statuses', [StatusEnum::Created, StatusEnum::Failed])
            ->setParameter('now', $now)
            ->setParameter('staleTime', $staleTime)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(function (OrmOutboxEmail $ormEmail) {
            return $this->mapper->fromDoctrineOrm($ormEmail);
        }, $ormEmails);
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidEmailSenderValueObjectException
     * @throws InvalidTraceIdException
     */
    private function checkAndMapToDomain(?object $ormOutgoingEmail): ?OutboxEmail
    {
        if (false === $ormOutgoingEmail instanceof OrmOutboxEmail) {
            return null;
        }

        return $this->mapper->fromDoctrineOrm($ormOutgoingEmail);
    }
}
