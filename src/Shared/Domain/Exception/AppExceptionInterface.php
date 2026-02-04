<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use Throwable;

interface AppExceptionInterface extends Throwable
{
    public function getErrorCode(): string;

    /**
     * @return array<string, mixed>
     */
    public function getMessageData(): array;
}
