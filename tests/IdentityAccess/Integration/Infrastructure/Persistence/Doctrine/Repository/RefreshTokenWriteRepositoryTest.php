<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Repository\RefreshTokenReadRepositoryInterface;
use App\IdentityAccess\Domain\Repository\RefreshTokenWriteRepositoryInterface;
use App\IdentityAccess\Infrastructure\Persistence\Doctrine\Entity\OrmRefreshToken;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\Traits\IdentityAccessEntityManagerTrait;
use App\Tests\IdentityAccess\Support\Traits\RefreshTokenFactoryTrait;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use App\Tests\Shared\Support\Traits\EntityTechnicalMetadataTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\MockClock;

class RefreshTokenWriteRepositoryTest extends KernelTestCase
{
    use IdentityAccessEntityManagerTrait;
    use EntityTechnicalMetadataTrait;
    use RefreshTokenFactoryTrait;

    private RefreshTokenWriteRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(RefreshTokenWriteRepositoryInterface::class);
    }

    public function testSave(): void
    {
        $clock = new MockClock('2023-01-01 11:22:33');
        $refreshToken = $this->getRefreshTokenMother()->create(expiresAt: $clock->now()->modify('+1 hour'));

        $created = $this->repository->save($refreshToken);

        self::assertNotNull($created->getId());
        self::assertTrue($refreshToken->getTokenHash()->equals($created->getTokenHash()));
        self::assertTrue($refreshToken->getAccountUlid()->equals($created->getAccountUlid()));
        self::assertTrue($refreshToken->getAccountType()->equals($created->getAccountType()));
        self::assertTrue($refreshToken->getExpiresAt()->equals($created->getExpiresAt()));
    }

    public function testDelete(): void
    {
        $refreshToken = $this->getRefreshTokenFixture()->create();
        $this->clearEntityManager();

        $this->repository->delete($refreshToken);

        self::assertNull($this->getReadRepository()->findById($refreshToken->getId()));
    }

    public function testDeleteAllPrevious(): void
    {
        $ulid = UserAccountMother::DEFAULT_ULID;
        $accountType = IdentityTypeEnum::User;
        $refreshToken = $this->getRefreshTokenFixture()->create(
            tokenHash: 'token-hash-1',
            accountUlid: $ulid,
            accountType: $accountType
        );
        $refreshToken2 = $this->getRefreshTokenFixture()->create(
            tokenHash: 'token-hash-2',
            accountUlid: $ulid,
            accountType: $accountType
        );
        $this->clearEntityManager();

        $this->repository->deleteAllPrevious($refreshToken);

        self::assertNull($this->getReadRepository()->findById($refreshToken->getId()));
        self::assertNull($this->getReadRepository()->findById($refreshToken2->getId()));
    }

    public function testItSetsTechnicalMetadataOnSave(): void
    {
        $refreshToken = $this->getRefreshTokenFixture()->create();
        $id = $refreshToken->getId()->value();

        $this->clearEntityManager();

        $ormEntity = $this->findOrmEntity(OrmRefreshToken::class, $id);

        self::assertNotNull($ormEntity);
        $this->assertHasCreatedAt($ormEntity);
    }

    private function getReadRepository(): RefreshTokenReadRepositoryInterface
    {
        return self::getContainer()->get(RefreshTokenReadRepositoryInterface::class);
    }
}
