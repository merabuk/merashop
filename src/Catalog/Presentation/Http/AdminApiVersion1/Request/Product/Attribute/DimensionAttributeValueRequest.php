<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\Attribute;

use App\Catalog\Application\DTO\Product\AttributeValue\DimensionAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class DimensionAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('numeric')]
    public float $magnitude;

    #[Assert\NotBlank]
    // TODO[attribute value]: think about translations?
    public string $unit;

    public function toData(): DimensionAttributeValueData
    {
        return new DimensionAttributeValueData(
            magnitude: $this->magnitude,
            unit: $this->unit
        );
    }
}
