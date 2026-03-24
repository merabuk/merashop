<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\DimensionAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class DimensionAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('numeric')]
    public ?float $magnitude;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $unitOptionId;

    public function toData(): DimensionAttributeValueData
    {
        return new DimensionAttributeValueData(
            magnitude: $this->magnitude,
            unitOptionId: $this->unitOptionId
        );
    }
}
