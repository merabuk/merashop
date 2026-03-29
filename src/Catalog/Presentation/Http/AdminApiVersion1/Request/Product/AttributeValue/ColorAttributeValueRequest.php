<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\ColorAttributeValueData;
use App\Catalog\Domain\ValueObject\ProductAttributeValue\Value\ColorValue;
use Symfony\Component\Validator\Constraints as Assert;

final class ColorAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Regex(pattern: ColorValue::HEX_REGEX, groups: [self::BASE_GROUP])]
    public ?string $value;

    public function toValueData(): ColorAttributeValueData
    {
        return new ColorAttributeValueData(value: $this->value);
    }
}
