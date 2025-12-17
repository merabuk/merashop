<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

final class EntityIdMissingException extends LogicException
{
    public static function forEntity(string $className): self
    {
        return new self(sprintf('Identifier for "%s" is missing. Reconstitution failed.', $className));
    }
}
