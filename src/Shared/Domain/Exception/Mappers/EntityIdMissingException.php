<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Mappers;

use App\Shared\Domain\Exception\LogicException;

final class EntityIdMissingException extends LogicException
{
    public function getErrorCode(): string
    {
        return 'ENTITY_ID_MISSING_ERROR';
    }

    public static function forEntity(string $className): self
    {
        return new self(sprintf('Identifier for "%s" is missing. Reconstitution failed.', $className));
    }
}
