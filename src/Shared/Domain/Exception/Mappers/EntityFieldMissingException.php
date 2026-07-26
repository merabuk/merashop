<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Mappers;

use App\Shared\Domain\Exception\LogicException;

final class EntityFieldMissingException extends LogicException
{
    public function getErrorCode(): string
    {
        return 'ENTITY_FIELD_MISSING_ERROR';
    }

    public static function forEntityId(string $className): self
    {
        return new self(sprintf('Identifier for "%s" is missing. Reconstitution failed.', $className));
    }

    public static function forField(string $field, string $className): self
    {
        return new self(sprintf('Field %s for "%s" is missing. Reconstitution failed.', $field, $className));
    }
}
