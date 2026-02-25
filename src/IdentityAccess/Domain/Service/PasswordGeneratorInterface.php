<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Service;

use App\IdentityAccess\Domain\Exception\PasswordGenerateException;

interface PasswordGeneratorInterface
{
    /**
     * @throws PasswordGenerateException
     */
    public function generateClientSecret(): string;

    /**
     * @throws PasswordGenerateException
     */
    public function generateTemporaryAdminPassword(): string;
}
