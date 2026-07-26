<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\UrlAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class UrlAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Url(groups: [self::BASE_GROUP])]
    public ?string $value = null;

    public function toValueData(): AttributeValueDataInterface
    {
        return new UrlAttributeValueData(value: (string) $this->value);
    }
}
