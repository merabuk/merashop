<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Identity;

interface UlidGeneratorInterface
{
    public function next(): string;
}
