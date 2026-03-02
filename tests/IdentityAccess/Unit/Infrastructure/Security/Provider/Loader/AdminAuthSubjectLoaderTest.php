<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Provider\Loader;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountUlidException;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Infrastructure\Security\Provider\Loader\AdminAuthSubjectLoader;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\AdminAccountMother;
use PHPUnit\Framework\TestCase;

final class AdminAuthSubjectLoaderTest extends TestCase
{
    private AdminAccountReadRepositoryInterface $repository;
    private AdminAuthSubjectLoader $loader;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AdminAccountReadRepositoryInterface::class);
        $this->loader = $this->createLoader();
    }

    public function testLoadSuccess(): void
    {
        $admin = AdminAccountMother::createWithData();
        $this->repository->method('findByUlid')->willReturn($admin);

        $subject = $this->loader->load($admin->getUlid()->value());

        self::assertNotNull($subject);
        self::assertSame(IdentityTypeEnum::Admin, $subject->getType());
        self::assertSame($admin->getUlid()->value(), $subject->getUlid());
        self::assertSame($admin->getEmail()->value(), $subject->getUserIdentifier());
        self::assertSame($admin->getPasswordHash()->value(), $subject->getPassword());
        self::assertSame($admin->getRoles()->toStrings(), $subject->getRoles());
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

    private function createLoader(): AdminAuthSubjectLoader
    {
        return new AdminAuthSubjectLoader(readRepository: $this->repository);
    }
}
