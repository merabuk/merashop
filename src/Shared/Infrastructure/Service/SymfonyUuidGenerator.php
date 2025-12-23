<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\UuidGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class SymfonyUuidGenerator implements UuidGeneratorInterface
{
    public function nextV7(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
