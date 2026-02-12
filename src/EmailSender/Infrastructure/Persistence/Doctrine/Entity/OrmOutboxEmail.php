<?php

declare(strict_types=1);

namespace App\EmailSender\Infrastructure\Persistence\Doctrine\Entity;

use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Attempts;
use App\EmailSender\Domain\ValueObject\OutboxEmail\From;
use App\EmailSender\Domain\ValueObject\OutboxEmail\FromName;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Subject;
use App\EmailSender\Domain\ValueObject\OutboxEmail\To;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Type\OutboxEmail\DriverType;
use App\EmailSender\Infrastructure\Persistence\Doctrine\Type\OutboxEmail\StatusType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
#[ORM\Table(name: 'outbox_emails')]
#[ORM\Index(
    name: 'idx_outbox_emails_status_scheduled_at',
    columns: ['status', 'scheduled_at'],
    options: ['where' => "((status = '".StatusEnum::Created->value."'::".StatusType::NAME.") OR (status = '".StatusEnum::Failed->value."'::".StatusType::NAME.'))']
)]
#[ORM\Index(name: 'idx_outbox_emails_trace_id', columns: ['trace_id'])]
class OrmOutboxEmail
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT)]
    public private(set) ?int $id = null;

    #[ORM\Column(type: StatusType::NAME, options: ['default' => StatusEnum::Created->value])]
    public StatusEnum $status = StatusEnum::Created;

    #[ORM\Column(type: DriverType::NAME)]
    public DriverEnum $driver = DriverEnum::Log;

    #[ORM\Column(name: '`from`', type: Types::STRING, length: From::MAX_LENGTH)]
    public string $from;

    #[ORM\Column(type: Types::STRING, length: FromName::MAX_LENGTH, nullable: true)]
    public ?string $fromName = null;

    #[ORM\Column(name: '`to`', type: Types::STRING, length: To::MAX_LENGTH)]
    public string $to;

    #[ORM\Column(type: Types::STRING, length: Subject::MAX_LENGTH)]
    public string $subject;
    #[ORM\Column(type: Types::TEXT)]
    public string $body;

    /**
     * @var ?array<string, mixed>
     */
    #[ORM\Column(type: Types::JSONB, nullable: true)]
    public ?array $payload = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => Attempts::DEFAULT])]
    public int $attempts = Attempts::DEFAULT;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $scheduledAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $lockedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $errorMessage = null;

    #[ORM\Column(type: Types::GUID, nullable: true)]
    public ?string $traceId = null;
}
