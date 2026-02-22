<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Mapper;

use App\Shared\Domain\Exception\Mappers\IncompatibleMappedEntityException;

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
     *
     * @throws IncompatibleMappedEntityException
     */
    public function toDoctrineOrm(object $domain): object;

    /**
     * @param TOrm $orm
     *
     * @return TDomain
     *
     * @throws IncompatibleMappedEntityException
     */
    public function fromDoctrineOrm(object $orm): object;

    /**
     * @param TDomain $domain
     * @param TOrm    $orm
     *
     * @throws IncompatibleMappedEntityException
     */
    public function mapToExistingOrm(object $domain, object $orm): void;
}
