<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Domain\Entity\Attribute;
use App\Catalog\Domain\Entity\ProductAttributeValue;
use App\Catalog\Domain\ValueObject\AdminUlid;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('catalog.product_attribute_value_provider')]
interface ProductAttributeValueProviderInterface
{
    public static function getDefaultIndexName(): string;

    /**
     * @return ProductAttributeValue[]
     */
    public function handle(
        Attribute $attribute,
        AttributeValueDataInterface $data,
        AdminUlid $adminUlid,
    ): array;
}
