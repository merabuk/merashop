<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\Attribute;

use App\Catalog\Application\DTO\Product\AttributeValue\ColorAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class ColorAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank]
    public ?string $value;

    public function toData(): ColorAttributeValueData
    {
        return new ColorAttributeValueData(value: $this->value);
    }
}
