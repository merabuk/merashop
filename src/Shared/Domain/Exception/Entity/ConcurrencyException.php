<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception\Entity;

use App\Shared\Domain\Enum\ErrorCodeEnum;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;
use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Exception\Markers\ConflictExceptionInterface;

class ConcurrencyException extends LogicException implements ClientFacingExceptionInterface, ConflictExceptionInterface, EntityContextAwareExceptionInterface
{
    private const string ENTITY_NAME_KEY = 'entityName';

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
            self::ENTITY_NAME_KEY => $this->entityName,
        ];
    }

    public static function getEntityNameKey(): string
    {
        return self::ENTITY_NAME_KEY;
    }
}
