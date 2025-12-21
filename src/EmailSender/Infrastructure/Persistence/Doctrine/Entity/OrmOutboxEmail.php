<?php

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Entity;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\ValueObject\OutboxEmail\From;
use App\EmailSender\Domain\ValueObject\OutboxEmail\FromName;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Subject;
use App\EmailSender\Domain\ValueObject\OutboxEmail\To;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Type\OutboxEmail\DriverType;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Type\OutboxEmail\StatusType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
#[ORM\Table(name: 'email_sender_outbox_email')]
class OrmOutboxEmail
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    public private(set) ?int $id = null;

    #[ORM\Column(type: StatusType::NAME)]
    public ?StatusEnum $status = null;

    #[ORM\Column(type: DriverType::NAME)]
    public ?DriverEnum $driver = null;

    #[ORM\Column(type: Types::STRING, length: Subject::MAX_LENGTH)]
    public ?string $subject = null;

    #[ORM\Column(type: Types::STRING, length: From::MAX_LENGTH)]
    public ?string $from = null;

    #[ORM\Column(type: Types::STRING, length: FromName::MAX_LENGTH)]
    public ?string $fromName = null;

    #[ORM\Column(type: Types::STRING, length: To::MAX_LENGTH)]
    public ?string $to = null;
}
