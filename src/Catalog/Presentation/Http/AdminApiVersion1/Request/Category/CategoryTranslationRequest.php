<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Category;

use App\Catalog\Domain\DTO\CategoryTranslationData;
use App\Catalog\Domain\ValueObject\Category\Translation;
use Symfony\Component\Validator\Constraints as Assert;

final class CategoryTranslationRequest
{
    public const string BASE_GROUP = 'CategoryTranslationRequest';

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Length(max: Translation::NAME_MAX_LENGTH, groups: [self::BASE_GROUP])]
    public ?string $name = null;

    #[Assert\Length(max: Translation::DESCRIPTION_MAX_LENGTH, groups: [self::BASE_GROUP])]
    public ?string $description = null;

    public function toData(): CategoryTranslationData
    {
        return new CategoryTranslationData(
            name: (string) $this->name,
            description: $this->description
        );
    }
}
