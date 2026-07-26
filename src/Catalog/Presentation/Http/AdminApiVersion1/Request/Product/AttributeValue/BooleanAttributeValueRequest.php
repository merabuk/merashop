<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\BooleanAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

class BooleanAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Type(type: 'boolean', groups: [self::BASE_GROUP])]
    public ?bool $value = null;

    public function toValueData(): BooleanAttributeValueData
    {
        return new BooleanAttributeValueData(value: (bool) $this->value);
    }
}
