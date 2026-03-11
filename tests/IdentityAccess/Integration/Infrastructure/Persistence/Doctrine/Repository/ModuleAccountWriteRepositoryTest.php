<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\ModuleAccountWriteRepositoryInterface;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmModuleAccount;
use App\Tests\IdentityAccess\Support\Traits\IdentityAccessEntityManagerTrait;
use App\Tests\IdentityAccess\Support\Traits\ModuleAccountFactoryTrait;
use App\Tests\Shared\Support\Traits\EntityTechnicalMetadataTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ModuleAccountWriteRepositoryTest extends KernelTestCase
{
    use EntityTechnicalMetadataTrait;
    use IdentityAccessEntityManagerTrait;
    use ModuleAccountFactoryTrait;

    private ModuleAccountWriteRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(ModuleAccountWriteRepositoryInterface::class);
    }

    public function testSave(): void
    {
        $module = $this->getModuleAccountMother()->create();

        $created = $this->repository->save($module);

        self::assertNotNull($created->getId());
        self::assertTrue($module->getUlid()->equals($created->getUlid()));
        self::assertTrue($module->getClientId()->equals($created->getClientId()));
        self::assertTrue($module->getClientSecret()->equals($created->getClientSecret()));
        self::assertTrue($module->getScopes()->equals($created->getScopes()));
    }

    public function testDelete(): void
    {
        $module = $this->getModuleAccountFixture()->create();
        $this->clearEntityManager();

        $this->repository->delete($module);

        $this->assertNull($this->getReadRepository()->findById($module->getId()));
    }

    public function testItSetsTechnicalMetadataOnSave(): void
    {
        $module = $this->getModuleAccountFixture()->create();
        $id = $module->getId()->value();

        $this->clearEntityManager();

        $ormEntity = $this->findOrmEntity(OrmModuleAccount::class, $id);

        self::assertNotNull($ormEntity);
        $this->assertHasCreatedAt($ormEntity);
        $this->assertHasUpdatedAt($ormEntity);
    }

    private function getReadRepository(): ModuleAccountReadRepositoryInterface
    {
        return self::getContainer()->get(ModuleAccountReadRepositoryInterface::class);
    }
}
