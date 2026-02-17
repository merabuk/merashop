<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateAttributeRequest extends BaseAttributeRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $version;
}
