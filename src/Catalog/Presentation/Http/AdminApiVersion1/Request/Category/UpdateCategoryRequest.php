<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Category;

use App\Catalog\Application\Command\UpdateCategory\UpdateCategoryCommand;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCategoryRequest extends BaseCategoryRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $version;

    public function toCommand(int $id, string $adminUlid): UpdateCategoryCommand
    {
        return new UpdateCategoryCommand(
            id: $id,
            slug: $this->slug,
            parentId: $this->parentId,
            status: $this->status,
            translations: $this->translations,
            version: $this->version,
            adminUlid: $adminUlid,
        );
    }
}
