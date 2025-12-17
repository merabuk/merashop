<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Mapper;

use App\EmailSender\Domain\Entity\OutgoingEmail;
use App\EmailSender\Domain\Exception\InvalidEmailSenderValueObjectException;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\Driver;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\From;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\FromName;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\Id;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\Status;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\Subject;
use App\EmailSender\Domain\ValueObject\OutgoingEmail\To;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Entity\OrmOutgoingEmail;
use App\Shared\Domain\Exception\EntityIdMissingException;
use App\Shared\Domain\Exception\IncompatibleMappedEntityException;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\MapperInterface;
use App\Shared\Infrastructure\Persistence\Doctrine\Mapper\TypeCheckTrait;

/**
 * @implements MapperInterface<OutgoingEmail, OrmOutgoingEmail>
 */
class OutgoingEmailMapper implements MapperInterface
{
    use TypeCheckTrait;

    /**
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): OrmOutgoingEmail
    {
        $this->assertIsType(OutgoingEmail::class, $domain);

        /** @var OutgoingEmail $domain */
        $orm = new OrmOutgoingEmail();

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
    public function fromDoctrineOrm(object $orm): OutgoingEmail
    {
        $this->assertIsType(OrmOutgoingEmail::class, $orm);

        /* @var OrmOutgoingEmail $orm */

        return new OutgoingEmail(
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
        $this->assertIsType(OutgoingEmail::class, $domain);
        $this->assertIsType(OrmOutgoingEmail::class, $orm);
    }
}
