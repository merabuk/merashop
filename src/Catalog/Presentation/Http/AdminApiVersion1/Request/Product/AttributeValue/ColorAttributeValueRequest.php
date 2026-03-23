<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\ColorAttributeValueData;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\ColorValue;
use Symfony\Component\Validator\Constraints as Assert;

final class ColorAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: ColorValue::HEX_REGEX)]
    public ?string $value;

    public function toData(): ColorAttributeValueData
    {
        return new ColorAttributeValueData(value: $this->value);
    }
}
