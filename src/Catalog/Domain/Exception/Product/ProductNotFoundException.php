<?php

namespace App\Catalog\Domain\Exception\Product;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogDomainException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;
use App\Shared\Domain\Exception\Markers\NotFoundExceptionInterface;

final class ProductNotFoundException extends CatalogDomainException implements ClientFacingExceptionInterface, NotFoundExceptionInterface
{
    private function __construct(
        private readonly string $field,
        private readonly string $value,
    ) {
        parent::__construct();
    }

    public static function withId(int $id): self
    {
        return new self(field: 'id', value: (string) $id);
    }

    public function getErrorCode(): string
    {
        return ErrorCodeEnum::ProductNotFound->value;
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
