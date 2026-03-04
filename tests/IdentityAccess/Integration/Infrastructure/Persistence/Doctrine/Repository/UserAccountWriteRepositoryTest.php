<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\UserAccountWriteRepositoryInterface;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmUserAccount;
use App\Tests\IdentityAccess\Support\Traits\IdentityAccessEntityManagerTrait;
use App\Tests\IdentityAccess\Support\Traits\UserAccountFactoryTrait;
use App\Tests\Shared\Support\Traits\EntityTechnicalMetadataTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserAccountWriteRepositoryTest extends KernelTestCase
{
    use EntityTechnicalMetadataTrait;
    use IdentityAccessEntityManagerTrait;
    use UserAccountFactoryTrait;

    private UserAccountWriteRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(UserAccountWriteRepositoryInterface::class);
    }

    public function testSave(): void
    {
        $user = $this->getUserAccountMother()->create();

        $created = $this->repository->save($user);

        self::assertNotNull($created->getId());
        self::assertTrue($user->getUlid()->equals($created->getUlid()));
        self::assertTrue($user->getEmail()->equals($created->getEmail()));
        self::assertTrue($user->getPasswordHash()->equals($created->getPasswordHash()));
        self::assertTrue($user->getRoles()->equals($created->getRoles()));
    }

    public function testDelete(): void
    {
        $user = $this->getUserAccountFixture()->create();
        $this->clearEntityManager();

        $this->repository->delete($user);

        self::assertNull($this->getReadRepository()->findById($user->getId()));
    }

    public function testItSetsTechnicalMetadataOnSave(): void
    {
        $user = $this->getUserAccountFixture()->create();
        $id = $user->getId()->value();

        $this->clearEntityManager();

        $ormEntity = $this->findOrmEntity(OrmUserAccount::class, $id);

        self::assertNotNull($ormEntity);
        $this->assertHasCreatedAt($ormEntity);
        $this->assertHasUpdatedAt($ormEntity);
    }

    private function getReadRepository(): UserAccountReadRepositoryInterface
    {
        return self::getContainer()->get(UserAccountReadRepositoryInterface::class);
    }
}
