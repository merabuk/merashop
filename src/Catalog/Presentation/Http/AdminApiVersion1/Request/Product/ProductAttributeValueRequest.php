<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

use Symfony\Component\Validator\Constraints as Assert;

final class ProductAttributeValueRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $attributeId = null;

    #[Assert\NotNull]
    #[Assert\Type(type: ['string', 'int', 'bool', 'array'])]
    public mixed $value;
}
