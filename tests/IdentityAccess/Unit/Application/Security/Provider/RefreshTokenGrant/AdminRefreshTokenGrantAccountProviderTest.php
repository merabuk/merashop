<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Security\Provider\RefreshTokenGrant;

use App\IdentityAccess\Application\Security\Provider\RefreshTokenGrant\AdminRefreshTokenGrantAccountProvider;
use App\IdentityAccess\Domain\Repository\AdminAccountReadRepositoryInterface;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\AdminAccountMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AdminRefreshTokenGrantAccountProviderTest extends TestCase
{
    private AdminAccountReadRepositoryInterface&MockObject $readRepository;
    private AdminRefreshTokenGrantAccountProvider $provider;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(AdminAccountReadRepositoryInterface::class);

        $this->provider = $this->createProvider();
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(IdentityTypeEnum::Admin->value, $this->provider::getDefaultIndexName());
    }

    public function testItHandleCorrectlyAndReturnData(): void
    {
        $admin = AdminAccountMother::createWithData();

        $this->readRepository->expects(self::once())
            ->method('findByUlid')
            ->willReturn($admin);

        $result = $this->provider->handle($admin->getUlid()->value());

        self::assertNotNull($result);
        self::assertSame($admin->getUlid()->value(), $result->subjectUlid);
        self::assertSame(IdentityTypeEnum::Admin, $result->subjectType);
        self::assertSame($admin->getRoles()->toStrings(), $result->roles);
        self::assertSame([], $result->scopes);
    }

    public function testItHandleCorrectlyAndReturnNull(): void
    {
        $this->readRepository->expects(self::once())
            ->method('findByUlid')
            ->willReturn(null);

        $result = $this->provider->handle(AdminAccountMother::DEFAULT_ULID);

        self::assertNull($result);
    }

    private function createProvider(): AdminRefreshTokenGrantAccountProvider
    {
        return new AdminRefreshTokenGrantAccountProvider(readRepository: $this->readRepository);
    }
}
