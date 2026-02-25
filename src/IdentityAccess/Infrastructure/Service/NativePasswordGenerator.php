<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Service;

use App\IdentityAccess\Domain\Exception\PasswordGenerateException;
use App\IdentityAccess\Domain\Service\PasswordGeneratorInterface;
use Random\RandomException;

final class NativePasswordGenerator implements PasswordGeneratorInterface
{
    /**
     * @throws PasswordGenerateException
     */
    public function generateClientSecret(): string
    {
        try {
            return $this->baseGeneratePassword(20);
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
            return $this->baseGeneratePassword(10);
        } catch (RandomException $e) {
            throw new PasswordGenerateException(message: 'Failed to generate temporary admin password', previous: $e);
        }
    }

    /**
     * @throws RandomException
     */
    private function baseGeneratePassword(int $length): string
    {
        return bin2hex(random_bytes($length));
    }
}
