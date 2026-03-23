<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\FloatAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class FloatAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotNull]
    #[Assert\Type('float')]
    public ?float $value;

    public function toData(): FloatAttributeValueData
    {
        return new FloatAttributeValueData(value: $this->value);
    }
}
