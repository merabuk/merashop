<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Service;

use App\IdentityAccess\Domain\Exception\RandomGenerateException;

interface PasswordGeneratorInterface
{
    /**
     * @throws RandomGenerateException
     */
    public function generateClientSecret(): string;

    /**
     * @throws RandomGenerateException
     */
    public function generateTemporaryAdminPassword(): string;
}
