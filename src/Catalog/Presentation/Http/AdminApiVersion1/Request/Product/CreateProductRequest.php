<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

use App\Catalog\Application\Command\CreateProduct\CreateProductCommand;
use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue\BaseAttributeValueRequest;

class CreateProductRequest extends BaseProductRequest
{
    public function toCommand(string $adminUlid): CreateProductCommand
    {
        return new CreateProductCommand(
            sku: $this->sku,
            status: $this->status,
            prices: array_map(fn (ProductPriceRequest $p) => new ProductPriceData(
                amount: $p->amount,
                currency: $p->currency,
                type: $p->type,
                taxValue: $p->taxValue,
                taxType: $p->taxType,
                taxIncluded: $p->taxIncluded,
                validFrom: $p->validFrom,
                validTo: $p->validTo,
            ), $this->prices),
            categoryIds: $this->categoryIds,
            attributeValues: array_map(fn (BaseAttributeValueRequest $a) => new ProductAttributeValueData(
                attributeId: $a->attributeId,
                value: $a->toData(),
            ), $this->attributeValues),
            translations: array_map(fn (ProductTranslationRequest $t) => new ProductTranslationData(
                name: $t->name,
                description: $t->description,
            ), $this->translations),
            images: $this->images,
            adminUlid: $adminUlid,
        );
    }
}
