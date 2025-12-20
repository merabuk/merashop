<?php

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Entity;

use App\EmailSender\Domain\ValueObject\OutboxEmail\From;
use App\EmailSender\Domain\ValueObject\OutboxEmail\FromName;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Subject;
use App\EmailSender\Domain\ValueObject\OutboxEmail\To;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Table(name: 'email_sender_outgoing_email')]
class OrmOutboxEmail
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    public private(set) ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    public ?string $status = null;

    #[ORM\Column(type: Types::STRING, length: 50)]
    public ?string $driver = null;

    #[ORM\Column(type: Types::STRING, length: Subject::MAX_LENGTH)]
    public ?string $subject = null;

    #[ORM\Column(type: Types::STRING, length: From::MAX_LENGTH)]
    public ?string $from = null;

    #[ORM\Column(type: Types::STRING, length: FromName::MAX_LENGTH)]
    public ?string $fromName = null;

    #[ORM\Column(type: Types::STRING, length: To::MAX_LENGTH)]
    public ?string $to = null;
}
