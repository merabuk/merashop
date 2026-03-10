<?php

namespace App\EmailSender\Domain\Factory\Contract;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\EmailSender\Domain\Enum\OutboxEmail\StatusEnum;
use App\Shared\Domain\ValueObject\Identity\TraceId;
use DateTimeImmutable;

interface OutboxEmailFactoryInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function createFromTemplate(
        string $to,
        string $subject,
        string $template,
        array $context,
        TraceId $traceId,
    ): OutboxEmail;

    /**
     * @param array<string, mixed> $context
     */
    public function createForTest(
        StatusEnum $status,
        DriverEnum $driver,
        string $from,
        string $fromName,
        string $to,
        string $subject,
        string $body,
        array $context,
        TraceId $traceId,
        ?int $attempts = null,
        ?DateTimeImmutable $scheduledAt = null,
        ?DateTimeImmutable $lockedAt = null,
        ?string $errorMessage = null,
    ): OutboxEmail;
}
