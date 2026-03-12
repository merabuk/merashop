<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Domain\ValueObject\Attribute\Translation;
use Symfony\Component\Validator\Constraints as Assert;

final class AttributeTranslationRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: Translation::NAME_MAX_LENGTH)]
    public string $name;
}
