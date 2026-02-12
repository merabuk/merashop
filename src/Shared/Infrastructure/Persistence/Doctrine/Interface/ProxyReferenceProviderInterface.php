<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Interface;

interface ProxyReferenceProviderInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    public function getReference(string $className, int|string $id): object;
}
