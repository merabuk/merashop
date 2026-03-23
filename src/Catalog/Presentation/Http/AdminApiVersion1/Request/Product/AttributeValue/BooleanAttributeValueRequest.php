<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\BooleanAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

class BooleanAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('boolean')]
    public ?bool $value;

    public function toData(): BooleanAttributeValueData
    {
        return new BooleanAttributeValueData(value: $this->value);
    }
}
