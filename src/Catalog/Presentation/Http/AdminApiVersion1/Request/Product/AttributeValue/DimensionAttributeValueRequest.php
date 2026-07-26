<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\DimensionAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class DimensionAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Type(type: 'numeric', groups: [self::BASE_GROUP])]
    public ?float $magnitude = null;

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Positive(groups: [self::BASE_GROUP])]
    public ?int $unitOptionId = null;

    public function toValueData(): DimensionAttributeValueData
    {
        return new DimensionAttributeValueData(
            magnitude: (float) $this->magnitude,
            unitOptionId: (int) $this->unitOptionId
        );
    }
}
