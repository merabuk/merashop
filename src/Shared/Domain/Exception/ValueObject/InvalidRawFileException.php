<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\ValueObject;

final class InvalidRawFileException extends InvalidValueObjectExceptionInterface
{
    public static function fileNotFound(string $path): self
    {
        return new self(sprintf('Raw file not found at path "%s"', $path));
    }

    public static function fileNotReadable(string $path): self
    {
        return new self(sprintf('Raw file is not readable at path "%s"', $path));
    }
}
