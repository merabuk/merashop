<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Services\Identity;

use App\Shared\Domain\Exception\InvalidArgumentException;

final class InvalidUuidException extends InvalidArgumentException
{
    public static function becauseItIsNotAValidUuidV7(string $invalidValue): self
    {
        return new self(sprintf('The string "%s" is not a valid UUID v7', $invalidValue));
    }
}
