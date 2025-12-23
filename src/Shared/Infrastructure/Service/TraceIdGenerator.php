<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\TraceIdGeneratorInterface;
use App\Shared\Domain\Service\UuidGeneratorInterface;

final class TraceIdGenerator implements TraceIdGeneratorInterface
{
    public function __construct(
        private readonly UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    public function generate(): string
    {
        return $this->uuidGenerator->nextV7();
    }
}
