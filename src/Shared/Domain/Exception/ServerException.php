<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use App\Shared\Domain\Exception\Contracts\AppExceptionInterface;
use Exception;

abstract class ServerException extends Exception implements AppExceptionInterface
{
    abstract public function getErrorCode(): string;

    /**
     * @return array<string, mixed>
     */
    public function getMessageData(): array
    {
        return [];
    }
}
