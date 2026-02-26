<?php

declare(strict_types=1);

namespace App\IdentityAccess\Infrastructure\Service;

use App\IdentityAccess\Domain\Exception\RandomGenerateException;
use App\IdentityAccess\Domain\Service\PasswordGeneratorInterface;
use App\IdentityAccess\Domain\Service\RandomTokenGeneratorInterface;
use Random\RandomException;

final class NativeRandomStringGenerator implements PasswordGeneratorInterface, RandomTokenGeneratorInterface
{
    /**
     * @throws RandomGenerateException
     */
    public function generateClientSecret(): string
    {
        try {
            return $this->generateBytes(20);
        } catch (RandomException $e) {
            throw new RandomGenerateException(message: 'Failed to generate client secret', previous: $e);
        }
    }

    /**
     * @throws RandomGenerateException
     */
    public function generateTemporaryAdminPassword(): string
    {
        try {
            return $this->generateBytes(10);
        } catch (RandomException $e) {
            throw new RandomGenerateException(message: 'Failed to generate temporary admin password', previous: $e);
        }
    }

    public function generateRefreshToken(): string
    {
        try {
            return $this->generateBytes(32);
        } catch (RandomException $e) {
            throw new RandomGenerateException(message: 'Failed to generate refresh token', previous: $e);
        }
    }


    /**
     * @throws RandomException
     */
    private function generateBytes(int $length): string
    {
        return bin2hex(random_bytes($length));
    }
}
