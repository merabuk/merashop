<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Category;

use App\Catalog\Application\Command\CreateCategory\CreateCategoryCommand;

class CreateCategoryRequest extends BaseCategoryRequest
{
    public function toCommand(string $adminUlid): CreateCategoryCommand
    {
        return new CreateCategoryCommand(
            slug: $this->slug,
            parentId: $this->parentId,
            status: $this->status,
            translations: $this->translations,
            adminUlid: $adminUlid,
        );
    }
}
