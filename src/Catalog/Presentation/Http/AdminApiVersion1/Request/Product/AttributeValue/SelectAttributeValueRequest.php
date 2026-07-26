<?php

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\SelectAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

class SelectAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Positive(groups: [self::BASE_GROUP])]
    public ?int $value = null;

    public function toValueData(): SelectAttributeValueData
    {
        return new SelectAttributeValueData(optionId: (int) $this->value);
    }
}
