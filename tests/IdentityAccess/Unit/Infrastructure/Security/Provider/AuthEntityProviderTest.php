<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Provider;

use App\IdentityAccess\Infrastructure\Exception\InvalidAuthEntityException;
use App\IdentityAccess\Infrastructure\Security\Provider\AuthEntityProvider;
use App\IdentityAccess\Infrastructure\Security\Provider\AuthSubject;
use App\IdentityAccess\Infrastructure\Security\Provider\Loader\AuthSubjectLoaderInterface;
use App\Tests\IdentityAccess\Support\UserAccountMother;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;

final class AuthEntityProviderTest extends TestCase
{
    private ContainerInterface&MockObject $loaders;
    private AuthEntityProvider $provider;

    protected function setUp(): void
    {
        $this->loaders = $this->createMock(ContainerInterface::class);

        $this->provider = $this->createProvider();
    }

    public function testLoadUserByIdentifierSuccess(): void
    {
        $user = UserAccountMother::createWithData();
        $subject = AuthSubject::fromUserAccount($user);
        $type = $subject->getType()->value;
        $ulid = $subject->getUlid();
        $identifier = $type.AuthEntityProvider::SEPARATOR.$ulid;

        $loader = $this->createMock(AuthSubjectLoaderInterface::class);

        $this->loaders->method('has')->with($type)->willReturn(true);
        $this->loaders->method('get')->with($type)->willReturn($loader);

        $loader->expects(self::once())
            ->method('load')
            ->with($ulid)
            ->willReturn($subject);

        $result = $this->provider->loadUserByIdentifier($identifier);

        self::assertSame($subject, $result);
    }

    public function testThrowsExceptionWhenNoSeparator(): void
    {
        $identifier = 'invalid-identifier';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf("Given identifier '%s' does not contain a type prefix", $identifier));

        $this->provider->loadUserByIdentifier($identifier);
    }

    public function testThrowsExceptionOnUnsupportedType(): void
    {
        $type = 'unknown';
        $identifier = $type.AuthEntityProvider::SEPARATOR.'some-ulid';
        $this->loaders->method('has')->willReturn(false);

        $this->expectException(InvalidAuthEntityException::class);
        $this->expectExceptionMessage(sprintf("Container does not have auth entity loader for '%s' type", $type));

        $this->provider->loadUserByIdentifier($identifier);
    }

    private function createProvider(): AuthEntityProvider
    {
        return new AuthEntityProvider(loaders: $this->loaders);
    }
}
