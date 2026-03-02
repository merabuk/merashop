<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Security\Provider\ClientCredentialsGrant;

use App\IdentityAccess\Application\Exception\InvalidClientException;
use App\IdentityAccess\Application\Security\Provider\ClientCredentialsGrant\ModuleClientCredentialsGrantAccountProvider;
use App\IdentityAccess\Domain\Repository\ModuleAccountReadRepositoryInterface;
use App\IdentityAccess\Domain\Service\PasswordHasherInterface;
use App\Shared\Domain\Enum\IdentityTypeEnum;
use App\Tests\IdentityAccess\Support\ModuleAccountMother;
use PHPUnit\Framework\TestCase;

class ModuleClientCredentialsGrantAccountProviderTest extends TestCase
{
    private ModuleAccountReadRepositoryInterface $readRepository;
    private PasswordHasherInterface $passwordHasher;
    private ModuleClientCredentialsGrantAccountProvider $provider;

    protected function setUp(): void
    {
        $this->readRepository = $this->createMock(ModuleAccountReadRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(PasswordHasherInterface::class);

        $this->provider = $this->createProvider();
    }

    public function testGetDefaultIndexName(): void
    {
        self::assertSame(IdentityTypeEnum::Module->value, $this->provider::getDefaultIndexName());
    }

    public function testItHandleCorrectly(): void
    {
        $module = ModuleAccountMother::createWithData();

        $this->readRepository->expects(self::once())->method('findByClientId')->willReturn($module);
        $this->passwordHasher->expects(self::once())->method('verify')->willReturn(true);

        $result = $this->provider->handle($module->getClientId()->value(), 'client_secret');

        self::assertSame($module->getUlid()->value(), $result->subjectUlid);
        self::assertSame(IdentityTypeEnum::Module, $result->subjectType);
        self::assertSame([], $result->roles);
        self::assertSame($module->getScopes()->toStrings(), $result->scopes);
    }

    public function testThrowsExceptionWhenModuleNotFound(): void
    {
        $this->readRepository->expects(self::once())->method('findByClientId')->willReturn(null);

        $this->expectException(InvalidClientException::class);
        $this->provider->handle('client_id', 'client_secret');
    }

    public function testThrowsExceptionWhenPasswordIncorrect(): void
    {
        $module = ModuleAccountMother::createWithData();

        $this->readRepository->expects(self::once())->method('findByClientId')->willReturn($module);
        $this->passwordHasher->expects(self::once())->method('verify')->willReturn(false);

        $this->expectException(InvalidClientException::class);
        $this->provider->handle($module->getClientId()->value(), 'client_secret');
    }

    private function createProvider(): ModuleClientCredentialsGrantAccountProvider
    {
        return new ModuleClientCredentialsGrantAccountProvider(
            readRepository: $this->readRepository,
            passwordHasher: $this->passwordHasher,
        );
    }
}
