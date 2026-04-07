<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogConflictException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;

final class ProductAlreadyExistsException extends CatalogConflictException implements ClientFacingExceptionInterface
{
    public function __construct(
        private readonly string $field,
        private readonly string $value,
    ) {
        parent::__construct();
    }

    public static function becauseSkuAlreadyExists(string $sku): self
    {
        return new self(field: 'sku', value: $sku);
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
        return [
            'field' => $this->field,
            'value' => $this->value,
        ];
    }
}
