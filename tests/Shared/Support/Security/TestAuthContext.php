<?php

declare(strict_types=1);

namespace App\Tests\Shared\Support\Security;

use App\Shared\Application\Security\AuthEntityContextInterface;
use App\Shared\Application\Security\AuthIdentity;

final class TestAuthContext implements AuthEntityContextInterface
{
    private static ?AuthIdentity $identity = null;

    public function setIdentity(?AuthIdentity $identity): void
    {
        self::$identity = $identity;
    }

    public function getIdentity(): ?AuthIdentity
    {
        return self::$identity;
    }
}
