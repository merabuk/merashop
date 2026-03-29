<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

use App\Catalog\Application\Command\CreateProduct\CreateProductCommand;

class CreateProductRequest extends BaseProductRequest
{
    public function toCommand(string $adminUlid): CreateProductCommand
    {
        return new CreateProductCommand(
            sku: $this->sku,
            status: $this->status,
            prices: $this->mapAndGetPrices(),
            categoryIds: $this->categoryIds,
            attributeValues: $this->mapAndGetAttributeValues(),
            translations: $this->mapAndGetTranslations(),
            images: $this->images,
            adminUlid: $adminUlid,
        );
    }
}
