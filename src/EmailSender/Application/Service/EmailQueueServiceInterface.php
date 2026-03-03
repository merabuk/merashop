<?php

declare(strict_types=1);

namespace App\EmailSender\Application\Service;

interface EmailQueueServiceInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function queueEmail(string $emailType, string $to, array $context): void;
}
