<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

use App\Catalog\Application\DTO\Product\ProductAttributeValueData;
use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Application\DTO\Product\ProductTranslationData;
use App\Catalog\Domain\Enum\Product\StatusEnum;
use App\Catalog\Domain\ValueObject\Product\Sku;
use App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product\AttributeValue\BaseAttributeValueRequest;
use App\Shared\Presentation\Http\Request\ValidateLocalesTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

#[Assert\GroupSequenceProvider]
abstract class BaseProductRequest implements GroupSequenceProviderInterface
{
    use ValidateLocalesTrait;

    private const string BASE_GROUP = 'BaseProductRequest';
    private const string FULL_GROUP = 'Full';

    #[Assert\NotBlank]
    #[Assert\Length(min: Sku::MIN_LENGTH, max: Sku::MAX_LENGTH)]
    #[Assert\Regex(pattern: Sku::REGEX)]
    public ?string $sku;

    #[Assert\NotBlank]
    #[Assert\Choice(
        callback: 'getProductStatuses',
        message: 'catalog.product.status_invalid'
    )]
    public ?string $status;

    /**
     * @var ProductPriceRequest[] $prices
     */
    #[Assert\NotBlank(groups: [ProductPriceRequest::BASE_GROUP])]
    #[Assert\Count(min: 1, minMessage: 'catalog.product.prices_empty', groups: [ProductPriceRequest::BASE_GROUP])]
    #[Assert\Valid(groups: [ProductPriceRequest::BASE_GROUP])]
    public ?array $prices;

    /**
     * @var ?ProductTranslationRequest[] $translations
     */
    #[Assert\NotBlank(groups: [ProductTranslationRequest::BASE_GROUP])]
    #[Assert\Count(min: 1, minMessage: 'shared.common.translations_empty', groups: [ProductTranslationRequest::BASE_GROUP])]
    #[Assert\Valid(groups: [ProductTranslationRequest::BASE_GROUP])]
    public ?array $translations;

    /**
     * @var ?int[]
     */
    #[Assert\NotBlank(groups: [self::FULL_GROUP])]
    #[Assert\Count(min: 1, minMessage: 'catalog.product.categories_empty', groups: [self::FULL_GROUP])]
    #[Assert\All(constraints: [
        new Assert\NotBlank(),
        new Assert\Positive(),
    ], groups: [self::FULL_GROUP])]
    public ?array $categoryIds;

    /**
     * @var BaseAttributeValueRequest[]
     */
    #[Assert\NotBlank(groups: [BaseAttributeValueRequest::BASE_GROUP])]
    #[Assert\Valid(groups: [BaseAttributeValueRequest::BASE_GROUP])]
    public ?array $attributeValues;

    /**
     * @var ?string[]
     */
    #[Assert\NotBlank(groups: [self::FULL_GROUP])]
    #[Assert\Count(min: 1, minMessage: 'catalog.product.images_empty', groups: [self::FULL_GROUP])]
    #[Assert\All(constraints: [
        new Assert\NotBlank(),
        new Assert\Ulid(),
    ], groups: [self::FULL_GROUP])]
    public ?array $images;

    #[Assert\Callback(groups: [ProductPriceRequest::BASE_GROUP])]
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

    #[Assert\Callback(groups: [BaseAttributeValueRequest::BASE_GROUP])]
    public function validateUniqueAttributes(ExecutionContextInterface $context): void
    {
        $ids = array_map(fn (BaseAttributeValueRequest $v) => $v->attributeId, $this->attributeValues ?? []);

        $checkedExists = [];

        foreach ($ids as $i => $id) {
            if (isset($checkedExists[$id])) {
                $context->buildViolation('catalog.product.attribute_id_duplicate')
                    ->atPath("attributeValues[{$i}]")
                    ->addViolation();
            }
            $checkedExists[$id] = true;
        }
    }

    public function getGroupSequence(): array
    {
        $groups = [
            self::BASE_GROUP,
            ProductTranslationRequest::BASE_GROUP,
        ];

        if ($this->status === StatusEnum::Active->value) {
            $groups[] = ProductPriceRequest::BASE_GROUP;
            $groups[] = BaseAttributeValueRequest::BASE_GROUP;
            $groups[] = self::FULL_GROUP;
        }

        return $groups;
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

    /**
     * @return ProductPriceData[]
     */
    protected function mapAndGetPrices(): array
    {
        return array_map(fn (ProductPriceRequest $p) => $p->toData(), $this->prices);
    }

    /**
     * @return ProductAttributeValueData[]
     */
    protected function mapAndGetAttributeValues(): array
    {
        return array_map(fn (BaseAttributeValueRequest $v) => $v->toData(), $this->attributeValues);
    }

    /**
     * @return ProductTranslationData[]
     */
    protected function mapAndGetTranslations(): array
    {
        return array_map(fn (ProductTranslationRequest $t) => $t->toData(), $this->translations);
    }
}
