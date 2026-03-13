<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

abstract class BaseProductRequest
{
    use ValidateLocalesTrait;

    #[Assert\NotBlank]
    #[Assert\Length(min: Sku::MIN_LENGTH, max: Sku::MAX_LENGTH)]
    #[Assert\Regex(pattern: Sku::REGEX)]
    public ?string $sku;

    /**
     * @var ProductPriceRequest[] $prices
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'catalog.product.prices_empty')]
    #[Assert\Valid]
    public ?array $prices;

    #[Assert\NotBlank]
    #[Assert\Choice(
        callback: 'getProductStatuses',
        message: 'catalog.product.status_invalid'
    )]
    public ?string $status;

    /**
     * @var ?ProductTranslationRequest[] $translations
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'shared.common.translations_empty')]
    #[Assert\Valid]
    public ?array $translations;

    /**
     * @var ?int[]
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'catalog.product.categories_empty')]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Positive(),
    ])]
    public ?array $categoryIds;

    /**
     * @var ProductAttributeValueRequest[] $attributeValues
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'catalog.product.attribute_values_empty')]
    #[Assert\Valid]
    public ?array $attributeValues;

    /**
     * @var ?string[]
     */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'catalog.product.images_empty')]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Ulid(),
    ])]
    public ?array $images;

    #[Assert\Callback]
    public function validateUniquePrices(ExecutionContextInterface $context): void
    {
        if (!isset($this->prices)) {
            return;
        }

        $registry = [];
        foreach ($this->prices as $index => $price) {
            if (!isset($price->currency, $price->type)) {
                continue;
            }

            $key = sprintf('%s_%s', strtoupper($price->currency), $price->type);

            if (isset($registry[$key])) {
                $context->buildViolation('catalog.product.prices_duplicate')
                    ->atPath("prices[{$index}]")
                    ->setParameter('key', $key)
                    ->addViolation();
            }
            $registry[$key] = true;
        }
    }

    /**
     * @return string[]
     */
    public static function getProductStatuses(): array
    {
        return [
            StatusEnum::Active->value,
            StatusEnum::Draft->value,
        ];
    }

    /**
     * @return array<string, true>
     */
    protected function getTranslations(): array
    {
        return isset($this->translations)
            ? array_map(fn (ProductTranslationRequest $translation) => true, $this->translations)
            : [];
    }

    protected function getRequestTranslationKey(): string
    {
        return 'translations';
    }
}
