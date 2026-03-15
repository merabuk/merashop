<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Exception\Category;

use App\Catalog\Domain\Enum\ErrorCodeEnum;
use App\Catalog\Domain\Exception\CatalogConflictException;
use App\Shared\Domain\Exception\Contracts\ClientFacingExceptionInterface;

class CategoryAlreadyExistsException extends CatalogConflictException implements ClientFacingExceptionInterface
{
    private string $slug = 'slug';

    public static function becauseSlugAlreadyExists(string $slug): self
    {
        $exception = new self();
        $exception->slug = $slug;

        return $exception;
    }

    public function getErrorCode(): string
    {
        return ErrorCodeEnum::CategoryAlreadyExists->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMessageData(): array
    {
        return ['slug' => $this->slug];
    }
}
