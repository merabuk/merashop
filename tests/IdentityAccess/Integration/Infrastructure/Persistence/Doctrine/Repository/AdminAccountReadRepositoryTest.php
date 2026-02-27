<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\AdminAccount\EmailAddress;
use App\Tests\IdentityAccess\Support\Traits\AdminAccountFactoryTrait;
use App\Tests\IdentityAccess\Support\Traits\IdentityAccessEntityManagerTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AdminAccountReadRepositoryTest extends KernelTestCase
{
    use IdentityAccessEntityManagerTrait;
    use AdminAccountFactoryTrait;

    private AdminAccountReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(AdminAccountReadRepositoryInterface::class);
    }

    public function testFindById(): void
    {
        $admin = $this->getAdminAccountFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findById($admin->getId());

        self::assertNotNull($found);
        self::assertTrue($admin->getUlid()->equals($found->getUlid()));
        self::assertTrue($admin->getEmail()->equals($found->getEmail()));
        self::assertTrue($admin->getPasswordHash()->equals($found->getPasswordHash()));
        self::assertTrue($admin->getRoles()->equals($found->getRoles()));
        self::assertTrue($admin->getStatus()->equals($found->getStatus()));
        if ($admin->getPasswordChangedAt()) {
            self::assertTrue($admin->getPasswordChangedAt()->equals($found->getPasswordChangedAt()));
        } else {
            self::assertNull($found->getPasswordChangedAt());
        }
    }

    public function testFindByEmail(): void
    {
        $admin = $this->getAdminAccountFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByEmail($admin->getEmail());

        self::assertNotNull($found);
        self::assertTrue($admin->getId()->equals($found->getId()));
        self::assertTrue($admin->getUlid()->equals($found->getUlid()));
    }

    public function testFindByUlid(): void
    {
        $admin = $this->getAdminAccountFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByUlid($admin->getUlid());

        self::assertNotNull($found);
        self::assertTrue($admin->getId()->equals($found->getId()));
        self::assertTrue($admin->getEmail()->equals($found->getEmail()));
    }

    public function testExistsByEmail(): void
    {
        $email = EmailAddress::fromString('admin@example.com');

        self::assertFalse($this->repository->existsByEmail($email));

        $this->getAdminAccountFixture()->create(email: $email->value());
        $this->clearEntityManager();

        self::assertTrue($this->repository->existsByEmail($email));
    }
}
