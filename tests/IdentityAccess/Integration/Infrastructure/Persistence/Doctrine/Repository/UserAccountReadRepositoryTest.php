<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\UserAccount\EmailAddress;
use App\Tests\IdentityAccess\Support\Traits\IdentityAccessEntityManagerTrait;
use App\Tests\IdentityAccess\Support\Traits\UserAccountFactoryTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserAccountReadRepositoryTest extends KernelTestCase
{
    use IdentityAccessEntityManagerTrait;
    use UserAccountFactoryTrait;

    private UserAccountReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(UserAccountReadRepositoryInterface::class);
    }

    public function testFindById(): void
    {
        $user = $this->getUserAccountFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findById($user->getId());

        self::assertNotNull($found);
        self::assertTrue($user->getUlid()->equals($found->getUlid()));
        self::assertTrue($user->getEmail()->equals($found->getEmail()));
        self::assertTrue($user->getPasswordHash()->equals($found->getPasswordHash()));
        self::assertTrue($user->getRoles()->equals($found->getRoles()));
    }

    public function testFindByEmail(): void
    {
        $user = $this->getUserAccountFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByEmail($user->getEmail());

        self::assertNotNull($found);
        self::assertTrue($user->getId()->equals($found->getId()));
        self::assertTrue($user->getUlid()->equals($found->getUlid()));
    }

    public function testFindByUlid(): void
    {
        $user = $this->getUserAccountFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByUlid($user->getUlid());

        self::assertNotNull($found);
        self::assertTrue($user->getId()->equals($found->getId()));
        self::assertTrue($user->getEmail()->equals($found->getEmail()));
    }

    public function testExistsByEmail(): void
    {
        $email = EmailAddress::fromString('user@example.com');

        self::assertFalse($this->repository->existsByEmail($email));

        $this->getUserAccountFixture()->create(email: $email->value());
        $this->clearEntityManager();

        self::assertTrue($this->repository->existsByEmail($email));
    }
}
