<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\Attribute;

use App\Catalog\Application\DTO\Product\AttributeValue\IntegerAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class IntegerAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotNull]
    #[Assert\Type('int')]
    public int $value;

    public function toData(): IntegerAttributeValueData
    {
        return new IntegerAttributeValueData($this->value);
    }
}
