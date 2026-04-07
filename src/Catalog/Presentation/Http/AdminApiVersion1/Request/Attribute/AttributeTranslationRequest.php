<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use App\Catalog\Domain\ValueObject\Attribute\Translation;
use Symfony\Component\Validator\Constraints as Assert;

final class AttributeTranslationRequest
{
    public const string BASE_GROUP = 'AttributeTranslationRequest';

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Length(min: 1, max: Translation::NAME_MAX_LENGTH, groups: [self::BASE_GROUP])]
    public ?string $name = null;

    public function toData(): AttributeTranslationData
    {
        return new AttributeTranslationData(name: $this->name);
    }
}
