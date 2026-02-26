<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Application\Service;

use App\IdentityAccess\Application\DTO\OAuth2Data;
use App\IdentityAccess\Domain\Enum\GrantTypeEnum;
use App\IdentityAccess\Application\Exception\UnsupportedGrantTypeException;
use App\IdentityAccess\Application\Security\Grant\GrantHandlerInterface;
use App\IdentityAccess\Application\Service\OAuth2TokenService;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

final class OAuth2TokenServiceTest extends TestCase
{
    private ContainerInterface $container;
    private OAuth2TokenService $service;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->service = new OAuth2TokenService($this->container);
    }

    public function testItSuccessfullyDelegatesToProperHandler(): void
    {
        $grantType = GrantTypeEnum::Password;
        $data = $this->createMock(OAuth2Data::class);
        $data->method('getGrantType')->willReturn($grantType);

        $grantHandler = $this->createMock(GrantHandlerInterface::class);

        $this->container->method('has')->with($grantType->value)->willReturn(true);
        $this->container->method('get')->with($grantType->value)->willReturn($grantHandler);

        $grantHandler->expects(self::once())
            ->method('handle')
            ->with($data);

        $this->service->handle($data);
    }

    public function testThrowsExceptionWhenHandlerIsMissingInContainer(): void
    {
        $grantType = GrantTypeEnum::Password;
        $data = $this->createMock(OAuth2Data::class);
        $data->method('getGrantType')->willReturn($grantType);

        $this->container->method('has')->with($grantType->value)->willReturn(false);

        $this->expectException(UnsupportedGrantTypeException::class);
        $this->expectExceptionMessage(sprintf(
            "Container does not have a handler for '%s' grant type",
            $grantType->value
        ));

        $this->service->handle($data);
    }

    public function testThrowsExceptionWhenHandlerHasWrongType(): void
    {
        $grantType = GrantTypeEnum::Password;
        $data = $this->createMock(OAuth2Data::class);
        $data->method('getGrantType')->willReturn($grantType);

        $invalidHandler = new stdClass();

        $this->container->method('has')->with($grantType->value)->willReturn(true);
        $this->container->method('get')->with($grantType->value)->willReturn($invalidHandler);

        $this->expectException(UnsupportedGrantTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'Grant type handler %s is not an instance of %s',
            get_debug_type($invalidHandler),
            GrantHandlerInterface::class
        ));

        $this->service->handle($data);
    }
}
