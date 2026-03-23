<?php

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\MultiSelectAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class MultiSelectAttributeValueRequest extends BaseAttributeValueRequest
{
    /**
     * @var int[]
     */
    #[Assert\NotBlank]
    #[Assert\All([
        new Assert\Positive(),
    ])]
    public ?array $values;

    public function toData(): MultiSelectAttributeValueData
    {
        return new MultiSelectAttributeValueData(optionIds: $this->values);
    }
}
