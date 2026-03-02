<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Context;

use App\IdentityAccess\Infrastructure\Security\Context\CurrentAccessTokenContext;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CurrentAccessTokenContextTest extends TestCase
{
    public function testItSetsAndGetsTokenContext(): void
    {
        $jti = 'token-jti';
        $expiresAt = 1234;

        $context = new CurrentAccessTokenContext();
        $context->set(jti: $jti, expiresAt: $expiresAt);

        self::assertSame($jti, $context->getJti());
        self::assertSame($expiresAt, $context->getExpiresAt());
    }

    public function testItThrowsExceptionIfJtiNotSet(): void
    {
        $context = new CurrentAccessTokenContext();

        $this->expectException(RuntimeException::class);
        $context->getJti();
    }

    public function testItThrowsExceptionIfExpiresAtNotSet(): void
    {
        $context = new CurrentAccessTokenContext();

        $this->expectException(RuntimeException::class);
        $context->getExpiresAt();
    }

    public function testItResetsStateCorrectly(): void
    {
        $context = new CurrentAccessTokenContext();
        $context->set('some-jti', 9999);

        $context->reset();

        $this->expectException(RuntimeException::class);
        $context->getJti();
    }
}
