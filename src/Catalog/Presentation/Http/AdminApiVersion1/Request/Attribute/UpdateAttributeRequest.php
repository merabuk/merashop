<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use App\Catalog\Application\DTO\Attribute\AttributeTranslationData;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateAttributeRequest extends BaseAttributeRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $version;

    public function toCommand(int $id, string $adminUlid): UpdateAttributeCommand
    {
        return new UpdateAttributeCommand(
            id: $id,
            code: $this->code,
            type: $this->type,
            translations: array_map(fn (AttributeTranslationRequest $t) => new AttributeTranslationData(
                name: $t->name,
            ), $this->translations),
            version: $this->version,
            adminUlid: $adminUlid,
        );
    }
}
