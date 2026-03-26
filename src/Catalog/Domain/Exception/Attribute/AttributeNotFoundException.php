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
    ) {
        parent::__construct();
    }

    public static function withId(int $id): self
    {
        return new self('id', (string) $id);
    }

    public static function withUlid(string $ulid): self
    {
        return new self('ulid', $ulid);
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
}
