<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Service;

use App\EmailSender\Domain\ValueObject\OutboxEmail\Attempts;
use DateMalformedStringException;
use DateTimeImmutable;

final readonly class OutboxRetryPolicy
{
    public function __construct(
        private int $maxAttempts,
    ) {
    }

    public function shouldRetry(Attempts $attempts): bool
    {
        return $attempts->value() < $this->maxAttempts;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function calculateNextAttemptAt(Attempts $attempts, DateTimeImmutable $now): DateTimeImmutable
    {
        $delay = ($attempts->value() + 1) ** 2;

        return $now->modify("+{$delay} minutes");
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }
}
