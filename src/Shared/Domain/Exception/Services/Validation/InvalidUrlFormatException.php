<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Services\Validation;

class InvalidUrlFormatException extends InvalidUrlException
{
    public static function becauseItIsNotAValidUrl(string $value): self
    {
        return new self(sprintf('URL "%s" is not a valid format', $value));
    }

    /**
     * @param string[] $allowedSchemes
     */
    public static function becauseSchemeIsNotAllowed(string $value, array $allowedSchemes): self
    {
        return new self(sprintf('URL "%s" scheme is not allowed. Allowed schemes: %s', $value, implode(', ', $allowedSchemes)));
    }
}
