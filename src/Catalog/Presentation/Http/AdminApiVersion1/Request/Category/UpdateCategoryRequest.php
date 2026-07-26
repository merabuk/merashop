<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Category;

use App\Catalog\Application\Command\UpdateCategory\UpdateCategoryCommand;
use App\Catalog\Domain\ValueObject\Category\Version;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateCategoryRequest extends BaseCategoryRequest
{
    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $version = null;

    public function toCommand(int $id, string $adminUlid): UpdateCategoryCommand
    {
        return new UpdateCategoryCommand(
            id: $id,
            slug: (string) $this->slug,
            parentId: $this->parentId,
            status: (string) $this->status,
            translations: $this->mapAndGetTranslations(),
            version: $this->version ?? Version::getInitialValue(),
            adminUlid: $adminUlid,
        );
    }
}
