<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Service;

use App\Shared\Domain\Service\UlidGeneratorInterface;
use Symfony\Component\Uid\Ulid;

final class SymfonyUlidGenerator implements UlidGeneratorInterface
{
    public function next(): string
    {
        return Ulid::generate();
    }
}
