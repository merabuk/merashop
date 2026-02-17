<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Entity;

use App\Shared\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\ConflictExceptionInterface;
use App\Shared\Domain\Exception\LogicException;

class ConcurrencyException extends LogicException implements ConflictExceptionInterface
{
    private string $entityName = 'entity';

    public function getErrorCode(): string
    {
        return ErrorCodeEnum::ConcurrencyError->value;
    }

    public function withEntityName(string $entityName): self
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMessageData(): array
    {
        return [
            'entityName' => $this->entityName,
        ];
    }
}
