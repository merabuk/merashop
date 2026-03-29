<?php

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\MultiSelectAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class MultiSelectAttributeValueRequest extends BaseAttributeValueRequest
{
    /**
     * @var int[]
     */
    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\All(constraints: [
        new Assert\Positive(),
    ], groups: [self::BASE_GROUP])]
    public ?array $values;

    public function toValueData(): MultiSelectAttributeValueData
    {
        return new MultiSelectAttributeValueData(optionIds: $this->values);
    }
}
