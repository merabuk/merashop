<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Mappers;

use App\Shared\Domain\Exception\LogicException;

final class IncompatibleMappedEntityException extends LogicException
{
    public function getErrorCode(): string
    {
        return 'INCOMPATIBLE_MAPPED_ENTITY';
    }

    public static function expected(object $mapper, string $expectedClass, object $actualObject): self
    {
        return new self(sprintf(
            'Mapper %s expected instance of "%s", but got "%s".',
            $mapper::class,
            $expectedClass,
            $actualObject::class
        ));
    }
}
