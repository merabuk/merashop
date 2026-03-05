<?php

declare(strict_types=1);

namespace App\Tests\Customer\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\Customer\Domain\Exception\CustomerProfile\CustomerProfileNotFoundException;
use App\Customer\Domain\Repository\CustomerProfileReadRepositoryInterface;
use App\Customer\Domain\ValueObject\CustomerProfile\Ulid;
use App\Tests\Customer\Support\Traits\CustomerEntityManagerTrait;
use App\Tests\Customer\Support\Traits\CustomerProfileFactoryTrait;
use App\Tests\Shared\Support\Traits\ValueObjectAssertionTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CustomerProfileReadRepositoryTest extends KernelTestCase
{
    use CustomerEntityManagerTrait;
    use CustomerProfileFactoryTrait;
    use ValueObjectAssertionTrait;

    private CustomerProfileReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(CustomerProfileReadRepositoryInterface::class);
    }

    public function testFindById(): void
    {
        $customerProfile = $this->getCustomerProfileFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findById($customerProfile->getId());

        self::assertNotNull($found);
        self::assertTrue($customerProfile->getUserUlid()->equals($found->getUserUlid()));
        $this->assertVoEqualsOrNull($customerProfile->getFirstName(), $found->getFirstName());
        $this->assertVoEqualsOrNull($customerProfile->getLastName(), $found->getLastName());
        $this->assertVoEqualsOrNull($customerProfile->getPhoneNumber(), $found->getPhoneNumber());
    }

    public function testGetByUlidThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(CustomerProfileNotFoundException::class);

        $this->repository->getByUlid(Ulid::fromString($this->getCustomerProfileMother()::DEFAULT_USER_ULID));
    }

    public function testFindByUlid(): void
    {
        $customerProfile = $this->getCustomerProfileFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByUlid($customerProfile->getUserUlid());

        self::assertNotNull($found);
        self::assertTrue($customerProfile->getUserUlid()->equals($found->getUserUlid()));
    }

    public function testExistsByUlid(): void
    {
        $ulid = Ulid::fromString($this->getCustomerProfileMother()::DEFAULT_USER_ULID);

        self::assertFalse($this->repository->existsByUlid($ulid));

        $customerProfile = $this->getCustomerProfileFixture()->create();
        $this->clearEntityManager();

        self::assertTrue($this->repository->existsByUlid($customerProfile->getUserUlid()));
    }
}
