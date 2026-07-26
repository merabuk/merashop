<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Attribute;

use App\Catalog\Application\Command\CreateAttribute\CreateAttributeCommand;

class CreateAttributeRequest extends BaseAttributeRequest
{
    public function toCommand(string $adminUlid): CreateAttributeCommand
    {
        return new CreateAttributeCommand(
            code: (string) $this->code,
            type: (string) $this->type,
            translations: $this->mapAndGetTranslations(),
            options: $this->mapAndGetOptions(),
            adminUlid: $adminUlid,
        );
    }
}
