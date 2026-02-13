<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Service;

use App\IdentityAccess\Domain\Exception\PasswordGenerateException;
use Random\RandomException;

class PasswordGenerator
{
    /**
     * @throws PasswordGenerateException
     */
    public function generateClientSecret(): string
    {
        try {
            return bin2hex(random_bytes(20));
        } catch (RandomException $e) {
            throw new PasswordGenerateException(message: 'Failed to generate client secret', previous: $e);
        }
    }

    /**
     * @throws PasswordGenerateException
     */
    public function generateTemporaryAdminPassword(): string
    {
        try {
            return bin2hex(random_bytes(10));
        } catch (RandomException $e) {
            throw new PasswordGenerateException(message: 'Failed to generate temporary admin password', previous: $e);
        }
    }
}
