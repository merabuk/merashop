<?php

declare(strict_types=1);

namespace App\IdentityAccess\Domain\Service;

use App\IdentityAccess\Domain\Exception\RandomGenerateException;

interface RandomTokenGeneratorInterface
{
    /**
     * @throws RandomGenerateException
     */
    public function generateRefreshToken(): string;
}
