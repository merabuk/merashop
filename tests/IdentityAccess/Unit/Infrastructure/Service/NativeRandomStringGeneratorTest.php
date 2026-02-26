<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Service;

use App\IdentityAccess\Infrastructure\Service\NativeRandomStringGenerator;
use PHPUnit\Framework\TestCase;

final class NativeRandomStringGeneratorTest extends TestCase
{
    public function testItGeneratesTokensOfCorrectLength(): void
    {
        $generator = new NativeRandomStringGenerator();

        $token = $generator->generateRefreshToken();

        self::assertSame(64, mb_strlen($token));
        $this->assertOnRegularExpression($token);
    }

    public function testItGeneratesPasswordsOfCorrectLength(): void
    {
        $generator = new NativeRandomStringGenerator();

        $password = $generator->generateTemporaryAdminPassword();

        self::assertSame(20, mb_strlen($password));
        $this->assertOnRegularExpression($password);
    }

    public function testItGeneratesClientSecretsOfCorrectLength(): void
    {
        $generator = new NativeRandomStringGenerator();

        $password = $generator->generateClientSecret();

        self::assertSame(40, mb_strlen($password));
        $this->assertOnRegularExpression($password);
    }

    private function assertOnRegularExpression(string $string): void
    {
        self::assertMatchesRegularExpression('/^[a-f\d]+$/', $string);
    }
}
