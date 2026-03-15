<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogConflictException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;

class AttributeAlreadyExistsException extends CatalogConflictException implements ClientFacingExceptionInterface
{
    private string $attributeCode = 'code';

    public static function becauseAttributeCodeAlreadyExists(string $attributeCode): self
    {
        $exception = new self();
        $exception->attributeCode = $attributeCode;

        return $exception;
    }

    public function getErrorCode(): string
    {
        return ErrorCodeEnum::AttributeAlreadyExists->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMessageData(): array
    {
        return ['code' => $this->attributeCode];
    }
}
