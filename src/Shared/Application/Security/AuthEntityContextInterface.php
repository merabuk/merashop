<?php

declare(strict_types=1);

namespace App\Shared\Application\Security;

interface AuthEntityContextInterface
{
    public function getIdentity(): ?AuthIdentity;
}
