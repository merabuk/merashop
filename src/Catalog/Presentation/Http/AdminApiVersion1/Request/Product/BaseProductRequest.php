<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

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
    #[Assert\NotBlank]
    #[Assert\Count(min: 1, minMessage: 'catalog.product.prices_empty')]
    #[Assert\Valid]
    public ?array $prices;

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
    #[Assert\NotBlank(groups: [self::FULL_GROUP])]
    #[Assert\Count(min: 1, minMessage: 'catalog.product.categories_empty', groups: [self::FULL_GROUP])]
    #[Assert\All([
        new Assert\NotBlank(),
        new Assert\Positive(),
    ])]
    public ?array $categoryIds;

    /**
     * @var BaseAttributeValueRequest[]
     */
    #[Assert\NotBlank(groups: [self::FULL_GROUP])]
    #[Assert\Valid]
    public ?array $attributeValues;

    /**
     * @var ?string[]
     */
    #[Assert\NotBlank(groups: [self::FULL_GROUP])]
    #[Assert\Count(min: 1, minMessage: 'catalog.product.images_empty', groups: [self::FULL_GROUP])]
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

    #[Assert\Callback]
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
        $groups = [self::BASE_GROUP];

        if ($this->status === StatusEnum::Active->value) {
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
}
