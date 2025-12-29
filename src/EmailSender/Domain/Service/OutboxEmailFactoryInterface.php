<?php

namespace App\EmailSender\Domain\Service;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\Enum\OutboxEmail\DriverEnum;
use App\Shared\Domain\ValueObject\TraceId;

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
        DriverEnum $driver,
        string $from,
        string $fromName,
        string $to,
        string $subject,
        string $body,
        array $context,
        TraceId $traceId,
    ): OutboxEmail;
}
