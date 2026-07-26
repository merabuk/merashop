<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Application\DTO\Attribute\AttributeOptionTranslationData;
use App\Catalog\Domain\ValueObject\AttributeOption\Translation;
use Symfony\Component\Validator\Constraints as Assert;

final class AttributeOptionTranslationRequest
{
    public const string BASE_GROUP = 'AttributeOptionTranslationRequest';

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Length(min: 1, max: Translation::NAME_MAX_LENGTH, groups: [self::BASE_GROUP])]
    public ?string $value = null;

    public function toData(): AttributeOptionTranslationData
    {
        return new AttributeOptionTranslationData(value: (string) $this->value);
    }
}
