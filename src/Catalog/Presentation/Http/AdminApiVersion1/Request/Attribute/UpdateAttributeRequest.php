<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
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
            translations: $this->mapAndGetTranslations(),
            options: $this->mapAndGetOptions(),
            version: $this->version,
            adminUlid: $adminUlid,
        );
    }
}
