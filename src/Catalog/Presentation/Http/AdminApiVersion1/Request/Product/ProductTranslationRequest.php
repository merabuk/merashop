<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Catalog\Domain\ValueObject\Product\Translation;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductTranslationRequest
{
    public const string BASE_GROUP = 'ProductTranslationRequest';

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Length(max: Translation::NAME_MAX_LENGTH, groups: [self::BASE_GROUP])]
    public ?string $name = null;

    #[Assert\Optional(groups: [self::BASE_GROUP])]
    #[Assert\Length(max: Translation::DESCRIPTION_MAX_LENGTH, groups: [self::BASE_GROUP])]
    public ?string $description = null;

    public function toData(): ProductTranslationData
    {
        return new ProductTranslationData(
            name: (string) $this->name,
            description: $this->description,
        );
    }
}
