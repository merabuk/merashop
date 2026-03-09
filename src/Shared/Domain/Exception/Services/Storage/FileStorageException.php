<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Services\Storage;

use App\Shared\Domain\Exception\LogicException;

class FileStorageException extends LogicException
{
    public function getErrorCode(): string
    {
        return 'FILE_STORAGE_ERROR';
    }
}
