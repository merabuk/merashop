<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Repository\RefreshTokenReadRepositoryInterface;
use App\Tests\IdentityAccess\Support\Traits\IdentityAccessEntityManagerTrait;
use App\Tests\IdentityAccess\Support\Traits\RefreshTokenFactoryTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\MockClock;

class RefreshTokenReadRepositoryTest extends KernelTestCase
{
    use IdentityAccessEntityManagerTrait;
    use RefreshTokenFactoryTrait;

    private RefreshTokenReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(RefreshTokenReadRepositoryInterface::class);
    }

    public function testFindById(): void
    {
        $clock = new MockClock('2023-01-01 11:22:33');
        $refreshToken = $this->getRefreshTokenFixture()->create(expiresAt: $clock->now()->modify('+1 hour'));
        $this->clearEntityManager();

        $found = $this->repository->findById($refreshToken->getId());

        self::assertNotNull($found);
        self::assertTrue($refreshToken->getTokenHash()->equals($found->getTokenHash()));
        self::assertTrue($refreshToken->getAccountUlid()->equals($found->getAccountUlid()));
        self::assertTrue($refreshToken->getAccountType()->equals($found->getAccountType()));
        self::assertTrue($refreshToken->getExpiresAt()->equals($found->getExpiresAt()));
    }

    public function findByToken(): void
    {
        $clock = new MockClock('2023-01-01 11:22:33');
        $refreshToken = $this->getRefreshTokenFixture()->create(expiresAt: $clock->now()->modify('+1 hour'));
        $this->clearEntityManager();

        $found = $this->repository->findByToken($refreshToken->getTokenHash());

        self::assertNotNull($found);
        self::assertTrue($refreshToken->getId()->equals($found->getId()));
    }
}
