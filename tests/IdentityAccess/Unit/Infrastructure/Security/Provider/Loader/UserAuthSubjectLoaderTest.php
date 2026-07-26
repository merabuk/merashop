<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Provider\Loader;

use App\IdentityAccess\Domain\Exception\UserAccount\InvalidUserAccountUlidException;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Infrastructure\Security\Provider\Loader\UserAuthSubjectLoader;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use App\Tests\Shared\BaseUnitTest;
use PHPUnit\Framework\MockObject\MockObject;

final class UserAuthSubjectLoaderTest extends BaseUnitTest
{
    private UserAccountReadRepositoryInterface&MockObject $repository;
    private UserAuthSubjectLoader $loader;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserAccountReadRepositoryInterface::class);
        $this->loader = $this->createLoader();
    }

    public function testLoadSuccess(): void
    {
        $user = UserAccountMother::createWithData();
        $this->repository->method('findByUlid')->willReturn($user);

        $subject = $this->loader->load($user->getUlid()->value());

        self::assertNotNull($subject);
        self::assertSame(IdentityTypeEnum::User, $subject->getType());
        self::assertSame($user->getUlid()->value(), $subject->getUlid());
        self::assertSame($user->getEmail()->value(), $subject->getUserIdentifier());
        self::assertSame($user->getPasswordHash()->value(), $subject->getPassword());
        self::assertSame($user->getRoles()->toStrings(), $subject->getRoles());
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

    private function createLoader(): UserAuthSubjectLoader
    {
        return new UserAuthSubjectLoader(readRepository: $this->repository);
    }
}
