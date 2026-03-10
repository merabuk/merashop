<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\Repository\TemporaryImageReadRepositoryInterface;
use App\Tests\Catalog\Support\Traits\CatalogEntityManagerTrait;
use App\Tests\Catalog\Support\Traits\TemporaryImageFactoryTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class TemporaryImageReadRepositoryTest extends KernelTestCase
{
    use TemporaryImageFactoryTrait;
    use CatalogEntityManagerTrait;

    private TemporaryImageReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(TemporaryImageReadRepositoryInterface::class);
    }

    public function testFindById(): void
    {
        $temporaryImage = $this->getTemporaryImageFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findById($temporaryImage->getId());

        self::assertNotNull($found);
        self::assertTrue($temporaryImage->getUlid()->equals($found->getUlid()));
        self::assertTrue($temporaryImage->getPath()->equals($found->getPath()));
        self::assertTrue($temporaryImage->getContext()->equals($found->getContext()));
    }

    public function testFindByUlid(): void
    {
        $temporaryImage = $this->getTemporaryImageFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByUlid($temporaryImage->getUlid());

        self::assertNotNull($found);
        self::assertTrue($temporaryImage->getId()->equals($found->getId()));
    }
}
