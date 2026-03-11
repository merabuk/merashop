<?php

declare(strict_types=1);

namespace App\Shared\Domain\Service\Validation;

use App\Shared\Domain\ValueObject\File\RawFile;

interface ImageValidatorInterface
{
    public function validate(RawFile $file, string $context, string $propertyPath = 'image'): void;
}
