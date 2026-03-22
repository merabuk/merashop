<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\Attribute;

use App\Catalog\Application\DTO\Product\AttributeValue\AttributeValueDataInterface;
use App\Catalog\Application\DTO\Product\AttributeValue\UrlAttributeValueData;
use Symfony\Component\Validator\Constraints as Assert;

final class UrlAttributeValueRequest extends BaseAttributeValueRequest
{
    #[Assert\NotBlank]
    #[Assert\Url]
    public ?string $value;

    public function toData(): AttributeValueDataInterface
    {
        return new UrlAttributeValueData(value: $this->value);
    }
}
