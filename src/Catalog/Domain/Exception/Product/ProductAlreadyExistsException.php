<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogConflictException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;

final class ProductAlreadyExistsException extends CatalogConflictException implements ClientFacingExceptionInterface
{
    private string $sku = 'sku';

    public static function becauseSkuAlreadyExists(string $sku): self
    {
        $exception = new self();
        $exception->sku = $sku;

        return $exception;
    }

    public function getErrorCode(): string
    {
        return ErrorCodeEnum::ProductAlreadyExists->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMessageData(): array
    {
        return ['sku' => $this->sku];
    }
}
