<?php

declare(strict_types=1);

namespace App\Tests\Customer\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Customer\Domain\Repository\CustomerProfileReadRepositoryInterface;
use App\Customer\Domain\Repository\CustomerProfileWriteRepositoryInterface;
use App\Customer\Infrastructure\Persistence\Doctrine\Entity\OrmCustomerProfile;
use App\Tests\Customer\Support\Traits\CustomerEntityManagerTrait;
use App\Tests\Customer\Support\Traits\CustomerProfileFactoryTrait;
use App\Tests\Shared\Support\Traits\EntityTechnicalMetadataTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CustomerProfileWriteRepositoryTest extends KernelTestCase
{
    use EntityTechnicalMetadataTrait;
    use CustomerEntityManagerTrait;
    use CustomerProfileFactoryTrait;
    use ValueObjectAssertionTrait;

    private CustomerProfileWriteRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(CustomerProfileWriteRepositoryInterface::class);
    }

    public function testSave(): void
    {
        $customerProfile = $this->getCustomerProfileMother()->create();

        $created = $this->repository->save($customerProfile);

        $this->assertNotNull($created->getId());
        self::assertTrue($customerProfile->getUserUlid()->equals($created->getUserUlid()));
        $this->assertVoEqualsOrNull($customerProfile->getFirstName(), $created->getFirstName());
        $this->assertVoEqualsOrNull($customerProfile->getLastName(), $created->getLastName());
        $this->assertVoEqualsOrNull($customerProfile->getPhoneNumber(), $created->getPhoneNumber());
    }

    public function testDelete(): void
    {
        $customerProfile = $this->getCustomerProfileFixture()->create();
        $this->clearEntityManager();

        $this->repository->delete($customerProfile);

        self::assertNull($this->getReadRepository()->findById($customerProfile->getId()));
    }

    public function testItSetsTechnicalMetadataOnSave(): void
    {
        $customerProfile = $this->getCustomerProfileFixture()->create();
        $id = $customerProfile->getId()->value();

        $this->clearEntityManager();

        $ormEntity = $this->findOrmEntity(OrmCustomerProfile::class, $id);

        self::assertNotNull($ormEntity);
        $this->assertHasCreatedAt($ormEntity);
        $this->assertHasUpdatedAt($ormEntity);
    }

    private function getReadRepository(): CustomerProfileReadRepositoryInterface
    {
        return self::getContainer()->get(CustomerProfileReadRepositoryInterface::class);
    }
}
