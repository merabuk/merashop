<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Provider\Loader;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountUlidException;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Infrastructure\Security\Provider\Loader\ModuleAuthSubjectLoader;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Shared\Domain\Enum\RoleEnum;
use App\Tests\IdentityAccess\Support\ModuleAccountMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\MockObject\MockObject;

final class ModuleAuthSubjectLoaderTest extends BaseUnitTest
{
    private ModuleAccountReadRepositoryInterface&MockObject $repository;
    private ModuleAuthSubjectLoader $loader;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ModuleAccountReadRepositoryInterface::class);
        $this->loader = $this->createLoader();
    }

    public function testLoadSuccess(): void
    {
        $module = ModuleAccountMother::createWithData();
        $this->repository->method('findByUlid')->willReturn($module);

        $subject = $this->loader->load($module->getUlid()->value());

        self::assertNotNull($subject);
        self::assertSame(IdentityTypeEnum::Module, $subject->getType());
        self::assertSame($module->getUlid()->value(), $subject->getUlid());
        self::assertSame($module->getClientId()->value(), $subject->getUserIdentifier());
        self::assertSame($module->getClientSecret()->value(), $subject->getPassword());
        self::assertSame([...$module->getScopes()->toStrings(), RoleEnum::Module->value], $subject->getRoles());
    }

    public function testLoadReturnsNullIfNotFound(): void
    {
        $this->repository->method('findByUlid')->willReturn(null);

        self::assertNull($this->loader->load('some-ulid'));
    }

    public function testLoadReturnsNullOnInvalidUlidString(): void
    {
        $this->repository->method('findByUlid')
            ->willThrowException(new InvalidUserAccountUlidException());

        self::assertNull($this->loader->load('not-a-ulid'));
    }

    private function createLoader(): ModuleAuthSubjectLoader
    {
        return new ModuleAuthSubjectLoader(readRepository: $this->repository);
    }
}
