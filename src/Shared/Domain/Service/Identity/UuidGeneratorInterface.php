<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Identity;

interface UuidGeneratorInterface
{
    public function nextV7(): string;
}
