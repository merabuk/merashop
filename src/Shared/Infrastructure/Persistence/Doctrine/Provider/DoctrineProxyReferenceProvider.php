<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence\Doctrine\Provider;

use App\Shared\Infrastructure\Persistence\Doctrine\Interface\ProxyReferenceProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use RuntimeException;

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
        $reference = $this->em->getReference($className, $id);

        if (is_object($reference)) {
            return $reference;
        }

        throw new RuntimeException(sprintf('Given reference is not an object. Type: %s', get_debug_type($reference)));
    }
}
