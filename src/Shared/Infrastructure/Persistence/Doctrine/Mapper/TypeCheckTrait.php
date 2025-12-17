<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Mapper;

use App\Shared\Domain\Exception\IncompatibleMappedEntityException;

trait TypeCheckTrait
{
    /**
     * @param class-string<T> $class
     *
     * @throws IncompatibleMappedEntityException
     *
     * @template T of object
     *
     * @phpstan-assert T $object
     */
    private function assertIsType(string $class, object $object): void
    {
        if (!$object instanceof $class) {
            throw IncompatibleMappedEntityException::expected(mapper: $this, expectedClass: $class, actualObject: $object);
        }
    }
}
