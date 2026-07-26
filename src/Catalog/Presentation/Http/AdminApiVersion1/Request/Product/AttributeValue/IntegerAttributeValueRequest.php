<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\IntegerAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class IntegerAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotNull(groups: [self::BASE_GROUP])]
    #[Assert\Type(type: 'int', groups: [self::BASE_GROUP])]
    public ?int $value = null;

    public function toValueData(): IntegerAttributeValueData
    {
        return new IntegerAttributeValueData(value: (int) $this->value);
    }
}
