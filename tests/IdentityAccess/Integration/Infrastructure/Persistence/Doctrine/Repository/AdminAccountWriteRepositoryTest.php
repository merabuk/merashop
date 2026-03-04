<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\AdminAccountWriteRepositoryInterface;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmAdminAccount;
use App\Tests\IdentityAccess\Support\Traits\AdminAccountFactoryTrait;
use App\Tests\IdentityAccess\Support\Traits\IdentityAccessEntityManagerTrait;
use App\Tests\Shared\Support\Traits\EntityTechnicalMetadataTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AdminAccountWriteRepositoryTest extends KernelTestCase
{
    use AdminAccountFactoryTrait;
    use EntityTechnicalMetadataTrait;
    use IdentityAccessEntityManagerTrait;
    use ValueObjectAssertionTrait;

    private AdminAccountWriteRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(AdminAccountWriteRepositoryInterface::class);
    }

    public function testSave(): void
    {
        $admin = $this->getAdminAccountMother()->create();

        $created = $this->repository->save($admin);

        self::assertNotNull($created->getId());
        self::assertTrue($admin->getUlid()->equals($created->getUlid()));
        self::assertTrue($admin->getEmail()->equals($created->getEmail()));
        self::assertTrue($admin->getPasswordHash()->equals($created->getPasswordHash()));
        self::assertTrue($admin->getRoles()->equals($created->getRoles()));
        self::assertTrue($admin->getStatus()->equals($created->getStatus()));
        $this->assertVoEqualsOrNull($admin->getPasswordChangedAt(), $created->getPasswordChangedAt());
    }

    public function testDelete(): void
    {
        $admin = $this->getAdminAccountFixture()->create();
        $this->clearEntityManager();

        $this->repository->delete($admin);

        $readRepository = self::getContainer()->get(AdminAccountReadRepositoryInterface::class);

        self::assertNull($readRepository->findByUlid($admin->getUlid()));
    }

    public function testItSetsTechnicalMetadataOnSave(): void
    {
        $admin = $this->getAdminAccountFixture()->create();
        $id = $admin->getId()->value();

        $this->clearEntityManager();

        $ormEntity = $this->findOrmEntity(OrmAdminAccount::class, $id);

        self::assertNotNull($ormEntity);
        $this->assertHasCreatedAt($ormEntity);
        $this->assertHasUpdatedAt($ormEntity);
    }
}
