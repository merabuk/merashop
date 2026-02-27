<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Security\Provider\PasswordGrant;

use App\IdentityAccess\Application\Exception\InvalidCredentialsException;
use App\IdentityAccess\Application\Security\Provider\PasswordGrant\UserPasswordGrantAccountProvider;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use PHPUnit\Framework\TestCase;

class UserPasswordGrantAccountProviderTest extends TestCase
{
    private UserAccountReadRepositoryInterface $readRepository;
    private PasswordHasherInterface $passwordHasher;
    private UserPasswordGrantAccountProvider $provider;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(UserAccountReadRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);

        $this->provider = $this->createProvider();
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(IdentityTypeEnum::User->value, $this->provider::getDefaultIndexName());
    }

    public function testItHandleCorrectly(): void
    {
        $user = UserAccountMother::createWithData();

        $this->readRepository->expects(self::once())->method('findByEmail')->willReturn($user);
        $this->passwordHasher->expects(self::once())->method('verify')->willReturn(true);

        $result = $this->provider->handle($user->getEmail()->value(), 'password');

        self::assertSame($user->getUlid()->value(), $result->subjectUlid);
        self::assertSame(IdentityTypeEnum::User, $result->subjectType);
        self::assertSame($user->getRoles()->toStrings(), $result->roles);
        self::assertSame([], $result->scopes);
    }

    public function testThrowsExceptionWhenUserNotFound(): void
    {
        $this->readRepository->expects(self::once())->method('findByEmail')->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);
        $this->provider->handle(UserAccountMother::DEFAULT_EMAIL, 'password');
    }

    public function testThrowsExceptionWhenPasswordIsInvalid(): void
    {
        $user = UserAccountMother::createWithData();

        $this->readRepository->expects(self::once())->method('findByEmail')->willReturn($user);
        $this->passwordHasher->expects(self::once())->method('verify')->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);
        $this->provider->handle($user->getEmail()->value(), 'password');
    }

    private function createProvider(): UserPasswordGrantAccountProvider
    {
        return new UserPasswordGrantAccountProvider(
            readRepository: $this->readRepository,
            passwordHasher: $this->passwordHasher
        );
    }
}
