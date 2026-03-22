<?php

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\Attribute;

use App\Catalog\Application\DTO\Product\AttributeValue\SelectAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

class SelectAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $value;

    public function toData(): SelectAttributeValueData
    {
        return new SelectAttributeValueData($this->value);
    }
}
