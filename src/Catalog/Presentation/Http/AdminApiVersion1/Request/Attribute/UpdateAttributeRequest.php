<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Application\Command\UpdateAttribute\UpdateAttributeCommand;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateAttributeRequest extends BaseAttributeRequest
{
    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Positive(groups: [self::BASE_GROUP])]
    public ?int $version;

    public function toCommand(string $ulid, string $adminUlid): UpdateAttributeCommand
    {
        return new UpdateAttributeCommand(
            ulid: $ulid,
            code: $this->code,
            type: $this->type,
            translations: $this->mapAndGetTranslations(),
            options: $this->mapAndGetOptions(),
            version: $this->version,
            adminUlid: $adminUlid,
        );
    }
}
