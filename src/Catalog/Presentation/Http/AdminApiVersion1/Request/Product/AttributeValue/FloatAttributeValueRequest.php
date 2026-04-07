<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\FloatAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class FloatAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotNull(groups: [self::BASE_GROUP])]
    #[Assert\Type(type: 'float', groups: [self::BASE_GROUP])]
    public ?float $value;

    public function toValueData(): FloatAttributeValueData
    {
        return new FloatAttributeValueData(value: $this->value);
    }
}
