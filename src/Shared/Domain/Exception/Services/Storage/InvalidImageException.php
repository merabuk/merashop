<?php

namespace App\Shared\Domain\Exception\Services\Storage;

use App\Shared\Domain\Exception\LogicException;

class InvalidImageException extends LogicException
{
    public function getErrorCode(): string
    {
        return 'INVALID_IMAGE_ERROR';
    }
}
