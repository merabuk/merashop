<?php

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\Attribute;

use App\Catalog\Application\DTO\Product\AttributeValue\MultiSelectAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class MultiSelectAttributeValueRequest extends BaseAttributeValueRequest
{
    /**
     * @var int[]
     */
    #[Assert\NotBlank]
    #[Assert\All([
        new Assert\Positive()
    ])]
    public ?array $values;

    public function toData(): MultiSelectAttributeValueData
    {
        return new MultiSelectAttributeValueData($this->values);
    }
}
