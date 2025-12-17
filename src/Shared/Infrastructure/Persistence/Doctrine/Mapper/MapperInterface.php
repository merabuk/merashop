<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Mapper;

/**
 * @template TDomain of object
 * @template TOrm of object
 */
interface MapperInterface
{
    /**
     * @param TDomain $domain
     *
     * @return TOrm
     */
    public function toDoctrineOrm(object $domain): object;

    /**
     * @param TOrm $orm
     *
     * @return TDomain
     */
    public function fromDoctrineOrm(object $orm): object;

    /**
     * @param TDomain $domain
     * @param TOrm    $orm
     */
    public function mapToExistingOrm(object $domain, object $orm): void;
}
