<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Application\DTO\Attribute\AttributeOptionTranslationData;
use App\Catalog\Domain\ValueObject\AttributeOption\Translation;
use Symfony\Component\Validator\Constraints as Assert;

final class AttributeOptionTranslationRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: Translation::NAME_MAX_LENGTH)]
    public ?string $value = null;

    public function toData(): AttributeOptionTranslationData
    {
        return new AttributeOptionTranslationData(value: $this->value);
    }
}
