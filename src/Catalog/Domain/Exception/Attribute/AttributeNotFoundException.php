<?php

namespace App\Catalog\Domain\Exception\Attribute;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogDomainException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;
use App\Shared\Domain\Exception\Markers\NotFoundExceptionInterface;

class AttributeNotFoundException extends CatalogDomainException implements ClientFacingExceptionInterface, NotFoundExceptionInterface
{
    private function __construct(
        private readonly string $field,
        private readonly string $value,
        private readonly ?int $index = null,
    ) {
        parent::__construct();
    }

    public static function withId(int $id, ?int $index = null): self
    {
        return new self(field: 'id', value: (string) $id, index: $index);
    }

    public static function withUlid(string $ulid, ?int $index = null): self
    {
        return new self(field: 'ulid', value: $ulid, index: $index);
    }

    public function getErrorCode(): string
    {
        return ErrorCodeEnum::AttributeNotFound->value;
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

    public function getExtraData(): array
    {
        if (null === $this->index) {
            return [];
        }

        return [
            'index' => $this->index,
        ];
    }
}
