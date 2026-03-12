<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Category;

use App\Catalog\Domain\ValueObject\Category\Translation;
use Symfony\Component\Validator\Constraints as Assert;

final class CategoryTranslationRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: Translation::NAME_MAX_LENGTH)]
    public string $name;

    #[Assert\Length(max: Translation::DESCRIPTION_MAX_LENGTH)]
    public ?string $description = null;
}
