<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

use App\Catalog\Application\Command\UpdateProduct\UpdateProductCommand;
use App\Catalog\Domain\ValueObject\Product\Version;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateProductRequest extends BaseProductRequest
{
    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Positive(groups: [self::BASE_GROUP])]
    public ?int $version = null;

    public function toCommand(int $id, string $adminUlid): UpdateProductCommand
    {
        return new UpdateProductCommand(
            id: $id,
            sku: (string) $this->sku,
            status: (string) $this->status,
            prices: $this->mapAndGetPrices(),
            categoryIds: $this->categoryIds ?? [],
            attributeValues: $this->mapAndGetAttributeValues(),
            translations: $this->mapAndGetTranslations(),
            images: $this->images ?? [],
            version: $this->version ?? Version::getInitialValue(),
            adminUlid: $adminUlid,
        );
    }
}
