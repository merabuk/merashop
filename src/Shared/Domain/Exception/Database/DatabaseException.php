<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Database;

use App\Shared\Domain\Exception\LogicException;

abstract class DatabaseException extends LogicException
{
    public function getErrorCode(): string
    {
        return 'DATABASE_ERROR';
    }
}
