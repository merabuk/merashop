<?php

declare(strict_types=1);

namespace App\Catalog\Application\Service\Attribute\AttributeOption;

use App\Catalog\Application\DTO\Attribute\AttributeOptionData;
use App\Catalog\Domain\ValueObject\AttributeOption\Metadata\AttributeOptionMetadataInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('catalog.product_attribute_option_metadata_provider')]
interface AttributeOptionMetadataProviderInterface
{
    public static function getDefaultIndexName(): string;

    public function handle(AttributeOptionData $data): AttributeOptionMetadataInterface;
}
