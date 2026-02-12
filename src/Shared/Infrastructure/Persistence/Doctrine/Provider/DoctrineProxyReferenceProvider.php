<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Provider;

use App\Shared\Infrastructure\Persistence\Doctrine\Interface\ProxyReferenceProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;

final readonly class DoctrineProxyReferenceProvider implements ProxyReferenceProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @throws ORMException
     */
    public function getReference(string $className, int|string $id): object
    {
        return $this->em->getReference($className, $id);
    }
}
