<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Image;

use App\Shared\Domain\ValueObject\File\ImageConstraints;

interface ImageConstraintsRegistryInterface
{
    public function getConstraints(string $context): ImageConstraints;
}
