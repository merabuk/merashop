<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Category;

use App\Catalog\Application\DTO\Category\CategoryTranslationData;
use App\Catalog\Domain\Enum\Category\StatusEnum;
use App\Catalog\Domain\ValueObject\Category\Slug;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

abstract class BaseCategoryRequest implements GroupSequenceProviderInterface
{
    use ValidateLocalesTrait;

    protected const string BASE_GROUP = 'BaseCategoryRequest';

    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: Slug::MAX_LENGTH)]
    #[Assert\Regex(pattern: Slug::REGEX)]
    public ?string $slug = null;

    #[Assert\Positive]
    public ?int $parentId = null;

    #[Assert\NotBlank]
    #[Assert\Choice(
        callback: 'getCategoryStatuses',
        message: 'catalog.category.status_invalid'
    )]
    public ?string $status = null;

    /**
     * @var ?CategoryTranslationRequest[] $translations
     */
    #[Assert\NotBlank]
    #[Assert\Count(
        min: 1,
        minMessage: 'shared.common.translations_empty',
        groups: [CategoryTranslationRequest::BASE_GROUP],
    )]
    #[Assert\Valid(groups: [CategoryTranslationRequest::BASE_GROUP])]
    public ?array $translations = null;

    public function getGroupSequence(): array
    {
        return [self::BASE_GROUP, CategoryTranslationRequest::BASE_GROUP];
    }

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
     * @return array<string, true>
     */
    protected function getTranslations(): array
    {
        $translations = [];

        foreach ($this->translations ?? [] as $key => $translation) {
            $translations[(string) $key] = true;
        }

        return $translations;
    }

    protected function getRequestTranslationKey(): string
    {
        return 'translations';
    }

    /**
     * @return CategoryTranslationData[]
     */
    protected function mapAndGetTranslations(): array
    {
        return array_map(fn (CategoryTranslationRequest $t) => $t->toData(), $this->translations ?? []);
    }
}
