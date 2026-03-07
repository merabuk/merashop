<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Category;

use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Catalog\Domain\ValueObject\Category\Translation;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use Symfony\Component\Validator\Constraints as Assert;

abstract class BaseCategoryRequest
{
    use ValidateLocalesTrait;

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: Slug::MAX_LENGTH)]
    #[Assert\Regex(pattern: Slug::REGEX)]
    public ?string $slug;

    #[Assert\Positive]
    public ?int $parentId = null;

    #[Assert\NotBlank]
    #[Assert\Choice(
        callback: 'getCategoryStatuses',
        message: 'admin.api.v1.category.status.invalid'
    )]
    public ?string $status;

    /**
     * @var ?array<string, array{name: string, description?: string}> $translations
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'admin.api.v1.category.translations.empty')]
    #[Assert\All([
        new Assert\Collection(
            fields: [
                'name' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 1, max: Translation::NAME_MAX_LENGTH),
                ],
                'description' => [
                    new Assert\Optional([
                        new Assert\Length(max: Translation::DESCRIPTION_MAX_LENGTH),
                    ]),
                ],
            ],
            allowExtraFields: false
        ),
    ])]
    public ?array $translations;

    /**
     * @return string[]
     */
    public static function getCategoryStatuses(): array
    {
        return [
            StatusEnum::Active->value,
            StatusEnum::Inactive->value,
        ];
    }

    /**
     * @return array<string, array{name: string, description?: string}>
     */
    protected function getTranslations(): array
    {
        return $this->translations ?? [];
    }

    protected function getRequestTranslationKey(): string
    {
        return 'translations';
    }

    protected function getMissingTranslationKey(): string
    {
        return 'admin.api.v1.category.translations.missing';
    }

    protected function getInvalidTranslationKey(): string
    {
        return 'admin.api.v1.category.translations.invalid';
    }
}
