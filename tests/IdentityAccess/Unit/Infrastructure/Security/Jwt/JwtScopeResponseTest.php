<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Jwt;

use App\IdentityAccess\Infrastructure\Security\Jwt\JwtScopeResponse;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class JwtScopeResponseTest extends TestCase
{
    public function testItAddsScopesToExtraParams(): void
    {
        $response = new JwtScopeResponse();

        $scope1 = $this->createMock(ScopeEntityInterface::class);
        $scope1->method('getIdentifier')->willReturn('profile');

        $scope2 = $this->createMock(ScopeEntityInterface::class);
        $scope2->method('getIdentifier')->willReturn('email');

        $accessToken = $this->createMock(AccessTokenEntityInterface::class);
        $accessToken->method('getScopes')->willReturn([$scope1, $scope2]);

        $reflection = new ReflectionClass($response);
        $method = $reflection->getMethod('getExtraParams');

        $result = $method->invoke($response, $accessToken);

        self::assertArrayHasKey('scopes', $result);
        self::assertSame(['profile', 'email'], $result['scopes']);
    }
}
