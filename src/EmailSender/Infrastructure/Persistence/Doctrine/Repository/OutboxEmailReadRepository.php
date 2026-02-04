<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Repository;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\Repository\OutboxEmailReadRepositoryInterface;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Id;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutboxEmail;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Domain\Exception\ValueObject\InvalidTraceIdException;
use App\Shared\Domain\ValueObject\TraceId;
use App\Shared\Infrastructure\Persistence\Doctrine\Repository\ReadRepositoryTrait;
use DateTimeImmutable;

class OutboxEmailReadRepository extends BaseOutgoingEmailRepository implements OutboxEmailReadRepositoryInterface
{
    use ReadRepositoryTrait;

    /**
     * @throws InvalidEmailSenderValueObjectException
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidTraceIdException
     */
    public function findById(Id $id): ?OutboxEmail
    {
        $ormOutgoingEmail = $this->find($id->value());

        return $this->checkAndMapToDomain($ormOutgoingEmail);
    }

    /**
     * @throws EntityIdMissingException
     * @throws InvalidEmailSenderValueObjectException
     * @throws InvalidTraceIdException
     * @throws IncompatibleMappedEntityException
     */
    public function findByIdForUpdate(Id $id): ?OutboxEmail
    {
        $ormOutgoingEmail = $this->_findByIdForUpdate($id->value());

        return $this->checkAndMapToDomain($ormOutgoingEmail);
    }

    /**
     * @return array<int, OutboxEmail>
     *
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidEmailSenderValueObjectException
     * @throws InvalidTraceIdException
     */
    public function findReadyToProcess(int $limit, DateTimeImmutable $now, DateTimeImmutable $staleTime): array
    {
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

    public function existsByTraceId(TraceId $traceId): bool
    {
        return $this->_existsBy([
            [
                'field' => 'traceId',
                'value' => $traceId->value(),
            ],
        ]);
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
