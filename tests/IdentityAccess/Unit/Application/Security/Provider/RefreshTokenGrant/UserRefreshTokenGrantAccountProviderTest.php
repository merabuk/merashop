<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Security\Provider\RefreshTokenGrant;

use App\IdentityAccess\Application\Security\Provider\RefreshTokenGrant\UserRefreshTokenGrantAccountProvider;
use App\IdentityAccess\Domain\Repository\UserAccountReadRepositoryInterface;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UserRefreshTokenGrantAccountProviderTest extends TestCase
{
    private UserAccountReadRepositoryInterface&MockObject $readRepository;
    private UserRefreshTokenGrantAccountProvider $provider;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(UserAccountReadRepositoryInterface::class);

        $this->provider = $this->createProvider();
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(IdentityTypeEnum::User->value, $this->provider::getDefaultIndexName());
    }

    public function testItHandleCorrectlyAndReturnData(): void
    {
        $user = UserAccountMother::createWithData();

        $this->readRepository->expects(self::once())
            ->method('findByUlid')
            ->willReturn($user);

        $result = $this->provider->handle($user->getUlid()->value());

        self::assertNotNull($result);
        self::assertSame($user->getUlid()->value(), $result->subjectUlid);
        self::assertSame(IdentityTypeEnum::User, $result->subjectType);
        self::assertSame($user->getRoles()->toStrings(), $result->roles);
        self::assertSame([], $result->scopes);
    }

    public function testItHandleCorrectlyAndReturnNull(): void
    {
        $this->readRepository->expects(self::once())
            ->method('findByUlid')
            ->willReturn(null);

        $result = $this->provider->handle(UserAccountMother::DEFAULT_ULID);

        self::assertNull($result);
    }

    private function createProvider(): UserRefreshTokenGrantAccountProvider
    {
        return new UserRefreshTokenGrantAccountProvider(readRepository: $this->readRepository);
    }
}
