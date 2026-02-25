<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\AdminAccountWriteRepositoryInterface;
use App\Tests\IdentityAccess\Support\Traits\AdminAccountFactoryTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AdminAccountWriteRepositoryTest extends KernelTestCase
{
    use AdminAccountFactoryTrait;

    private AdminAccountWriteRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(AdminAccountWriteRepositoryInterface::class);
    }

    public function testSaveSuccess(): void
    {
        $admin = $this->getAdminAccountMother()->create();

        $created = $this->repository->save($admin);

        self::assertNotNull($created->getId());
        self::assertTrue($admin->getUlid()->equals($created->getUlid()));
        self::assertTrue($admin->getEmail()->equals($created->getEmail()));
        self::assertTrue($admin->getPasswordHash()->equals($created->getPasswordHash()));
        self::assertTrue($admin->getRoles()->equals($created->getRoles()));
        self::assertTrue($admin->getStatus()->equals($created->getStatus()));
        if ($admin->getPasswordChangedAt()) {
            self::assertTrue($admin->getPasswordChangedAt()->equals($created->getPasswordChangedAt()));
        } else {
            self::assertNull($created->getPasswordChangedAt());
        }
    }

    public function testDeleteSuccess(): void
    {
        $admin = $this->getAdminAccountFixture()->create();

        $this->repository->delete($admin);

        $readRepository = self::getContainer()->get(AdminAccountReadRepositoryInterface::class);

        self::assertNull($readRepository->findByUlid($admin->getUlid()));
    }
}
