<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Mapper;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Driver;
use App\EmailSender\Domain\ValueObject\OutboxEmail\From;
use App\EmailSender\Domain\ValueObject\OutboxEmail\FromName;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Id;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Status;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Subject;
use App\EmailSender\Domain\ValueObject\OutboxEmail\To;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutboxEmail;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<OutboxEmail, OrmOutboxEmail>
 */
class OutboxEmailMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmOutboxEmail
    {
        $this->assertIsType(OutboxEmail::class, $domain);

        /** @var OutboxEmail $domain */
        $orm = new OrmOutboxEmail();

        $orm->status = $domain->getStatus()->value()->value;
        $orm->driver = $domain->getDriver()->value()->value;
        $orm->subject = $domain->getSubject()->value();
        $orm->from = $domain->getFrom()->value();
        $orm->fromName = $domain->getFromName()->value();
        $orm->to = $domain->getTo()->value();

        return $orm;
    }

    /**
     * @throws EntityIdMissingException
     * @throws IncompatibleMappedEntityException
     * @throws InvalidEmailSenderValueObjectException
     */
    public function fromDoctrineOrm(object $orm): OutboxEmail
    {
        $this->assertIsType(OrmOutboxEmail::class, $orm);

        /* @var OrmOutboxEmail $orm */

        return new OutboxEmail(
            id: Id::fromInt($orm->id ?? throw EntityIdMissingException::forEntity($orm::class)),
            status: Status::fromString($orm->status),
            driver: Driver::fromString($orm->driver),
            subject: Subject::fromString($orm->subject),
            from: From::fromString($orm->from),
            fromName: FromName::fromString($orm->fromName),
            to: To::fromString($orm->to),
        );
    }

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void
    {
        $this->assertIsType(OutboxEmail::class, $domain);
        $this->assertIsType(OrmOutboxEmail::class, $orm);
    }
}
