<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Integration\Infrastructure\Persistence\Doctrine\Repository;

use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\ValueObject\ModuleAccount\ClientId;
use App\Tests\IdentityAccess\Support\Traits\IdentityAccessEntityManagerTrait;
use App\Tests\IdentityAccess\Support\Traits\ModuleAccountFactoryTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ModuleAccountReadRepositoryTest extends KernelTestCase
{
    use IdentityAccessEntityManagerTrait;
    use ModuleAccountFactoryTrait;

    private ModuleAccountReadRepositoryInterface $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->repository = self::getContainer()->get(ModuleAccountReadRepositoryInterface::class);
    }

    public function testFindById(): void
    {
        $module = $this->getModuleAccountFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findById($module->getId());

        self::assertNotNull($found);
        self::assertTrue($module->getUlid()->equals($found->getUlid()));
        self::assertTrue($module->getClientId()->equals($found->getClientId()));
        self::assertTrue($module->getClientSecret()->equals($found->getClientSecret()));
        self::assertTrue($module->getScopes()->equals($found->getScopes()));
    }

    public function testFindByClientId(): void
    {
        $module = $this->getModuleAccountFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByClientId($module->getClientId());

        self::assertNotNull($found);
        self::assertTrue($module->getId()->equals($found->getId()));
        self::assertTrue($module->getUlid()->equals($found->getUlid()));
    }

    public function testFindByUlid(): void
    {
        $module = $this->getModuleAccountFixture()->create();
        $this->clearEntityManager();

        $found = $this->repository->findByUlid($module->getUlid());

        self::assertNotNull($found);
        self::assertTrue($module->getId()->equals($found->getId()));
        self::assertTrue($module->getClientId()->equals($found->getClientId()));
    }

    public function testExistsByClientId(): void
    {
        $clientId = ClientId::fromString('test-client-id');

        self::assertFalse($this->repository->existsByClientId($clientId));

        $this->getModuleAccountFixture()->create(clientId: $clientId->value());
        $this->clearEntityManager();

        self::assertTrue($this->repository->existsByClientId($clientId));
    }
}
