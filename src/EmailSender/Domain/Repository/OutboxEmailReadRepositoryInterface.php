<?php

declare(strict_types=1);

namespace App\EmailSender\Domain\Repository;

use App\EmailSender\Domain\Entity\OutboxEmail;
use App\EmailSender\Domain\ValueObject\OutboxEmail\Id;
use App\Shared\Domain\ValueObject\Identity\TraceId;
use DateTimeImmutable;

interface OutboxEmailReadRepositoryInterface
{
    public function findById(Id $id): ?OutboxEmail;

    public function findByIdForUpdate(Id $id): ?OutboxEmail;

    /**
     * @return array<int, OutboxEmail>
     */
    public function findReadyToProcess(int $limit, DateTimeImmutable $now, DateTimeImmutable $staleTime): array;

    public function existsByTraceId(TraceId $traceId): bool;
}
