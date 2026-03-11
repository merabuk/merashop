<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Security\Provider\PasswordGrant;

use App\IdentityAccess\Application\Exception\InvalidCredentialsException;
use App\IdentityAccess\Application\Security\Provider\PasswordGrant\AdminPasswordGrantAccountProvider;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\AdminAccountMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AdminPasswordGrantAccountProviderTest extends TestCase
{
    private AdminAccountReadRepositoryInterface&MockObject $readRepository;
    private PasswordHasherInterface&MockObject $passwordHasher;
    private AdminPasswordGrantAccountProvider $provider;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(AdminAccountReadRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);

        $this->provider = $this->createProvider();
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(IdentityTypeEnum::Admin->value, $this->provider::getDefaultIndexName());
    }

    public function testItHandleCorrectly(): void
    {
        $admin = AdminAccountMother::createWithData();

        $this->readRepository->expects(self::once())->method('findByEmail')->willReturn($admin);
        $this->passwordHasher->expects(self::once())->method('verify')->willReturn(true);

        $result = $this->provider->handle($admin->getEmail()->value(), 'password');

        self::assertSame($admin->getUlid()->value(), $result->subjectUlid);
        self::assertSame(IdentityTypeEnum::Admin, $result->subjectType);
        self::assertSame($admin->getRoles()->toStrings(), $result->roles);
        self::assertSame([], $result->scopes);
    }

    public function testThrowsExceptionWhenAdminNotFound(): void
    {
        $this->readRepository->expects(self::once())->method('findByEmail')->willReturn(null);

        $this->expectException(InvalidCredentialsException::class);
        $this->provider->handle(AdminAccountMother::DEFAULT_EMAIL, 'password');
    }

    public function testThrowsExceptionWhenPasswordIncorrect(): void
    {
        $admin = AdminAccountMother::createWithData();

        $this->readRepository->expects(self::once())->method('findByEmail')->willReturn($admin);
        $this->passwordHasher->expects(self::once())->method('verify')->willReturn(false);

        $this->expectException(InvalidCredentialsException::class);
        $this->provider->handle($admin->getEmail()->value(), 'password');
    }

    private function createProvider(): AdminPasswordGrantAccountProvider
    {
        return new AdminPasswordGrantAccountProvider(
            readRepository: $this->readRepository,
            passwordHasher: $this->passwordHasher
        );
    }
}
