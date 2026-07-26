<?php

declare(strict_types=1);

namespace App\Tests\IdentityAccess\Unit\Infrastructure\Security\Hasher;

use App\IdentityAccess\Infrastructure\Security\Hasher\TokenHasherService;
use App\Tests\Shared\BaseUnitTest;

final class TokenHasherServiceTest extends BaseUnitTest
{
    public function testItHashesTokenCorrectly(): void
    {
        $hasher = new TokenHasherService();
        $plainToken = 'test-token';

        $expectedHash = hash('sha256', $plainToken);

        $result = $hasher->hash($plainToken);

        self::assertSame($expectedHash, $result);
        self::assertSame(64, mb_strlen($result));
    }
}
