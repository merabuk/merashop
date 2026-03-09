<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service;

use App\Shared\Domain\ValueObject\ImageConstraints;

interface ImageConstraintsRegistryInterface
{
    public function getConstraints(string $context): ImageConstraints;
}
