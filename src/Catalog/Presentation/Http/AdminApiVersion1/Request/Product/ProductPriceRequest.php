<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

use App\Catalog\Application\DTO\Product\ProductPriceData;
use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Catalog\Domain\ValueObject\ProductPrice\Tax;
use App\Shared\Domain\Enum\CurrencyEnum;
use App\Shared\Domain\Enum\TaxTypeEnum;
use App\Shared\Domain\ValueObject\Temporal\DateTimeValueObject;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Throwable;

final class ProductPriceRequest
{
    public const string BASE_GROUP = 'ProductPriceRequest';

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\PositiveOrZero(groups: [self::BASE_GROUP])]
    public ?int $amount = null;

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Currency(message: 'catalog.product_price.currency_invalid_format', groups: [self::BASE_GROUP])]
    #[Assert\Choice(
        callback: 'getAvailableCurrencies',
        message: 'catalog.product_price.currency_not_supported',
        groups: [self::BASE_GROUP],
    )]
    public ?string $currency = null;

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Choice(
        callback: 'getProductPriceTypes',
        message: 'catalog.product_price.type_invalid',
        groups: [self::BASE_GROUP],
    )]
    public ?string $type = null;

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Choice(
        callback: 'getProductPriceTaxTypes',
        message: 'catalog.product_price.tax_type_invalid',
        groups: [self::BASE_GROUP],
    )]
    public ?string $taxType = null;

    #[Assert\NotBlank(groups: [self::BASE_GROUP])]
    #[Assert\Type(type: 'numeric', groups: [self::BASE_GROUP])]
    public ?float $taxValue = null;

    #[Assert\NotNull(groups: [self::BASE_GROUP])]
    #[Assert\Type(type: 'bool', groups: [self::BASE_GROUP])]
    public ?bool $taxIncluded = null;

    #[Assert\DateTime(format: DateTimeValueObject::INPUT_FORMAT, groups: [self::BASE_GROUP])]
    public ?string $validFrom = null;

    #[Assert\DateTime(format: DateTimeValueObject::INPUT_FORMAT, groups: [self::BASE_GROUP])]
    public ?string $validTo = null;

    public function toData(): ProductPriceData
    {
        return new ProductPriceData(
            amount: (int) $this->amount,
            currency: (string) $this->currency,
            type: (string) $this->type,
            taxValue: (float) $this->taxValue,
            taxType: (string) $this->taxType,
            taxIncluded: (bool) $this->taxIncluded,
            validFrom: $this->validFrom,
            validTo: $this->validTo,
        );
    }

    #[Assert\Callback(groups: [self::BASE_GROUP])]
    public function validateTaxValue(ExecutionContextInterface $context): void
    {
        if (null === $this->taxType) {
            return;
        }

        $invalidFixedTaxValue = $this->taxType === TaxTypeEnum::Fixed->value
            && ($this->taxValue < Tax::MIN_FIXED_TAX_VALUE || $this->taxValue > $this->amount);

        $invalidPercentageTaxValue = $this->taxType === TaxTypeEnum::Percentage->value
            && ($this->taxValue < Tax::MIN_PERCENTAGE_TAX_VALUE || $this->taxValue > Tax::MAX_PERCENTAGE_TAX_VALUE);

        if ($invalidFixedTaxValue || $invalidPercentageTaxValue) {
            $context->buildViolation('catalog.product_price.tax_value_invalid')
                ->atPath('taxValue')
                ->addViolation();
        }
    }

    #[Assert\Callback(groups: [self::BASE_GROUP])]
    public function validatePeriod(ExecutionContextInterface $context): void
    {
        if ($this->type === TypeEnum::Sale->value) {
            if (null === $this->validFrom) {
                $context->buildViolation('shared.common.field_required')
                    ->setParameter('field', 'validFrom')
                    ->atPath('validFrom')
                    ->addViolation();
            }
            if (null === $this->validTo) {
                $context->buildViolation('shared.common.field_required')
                    ->setParameter('field', 'validTo')
                    ->atPath('validTo')
                    ->addViolation();
            }
        }

        if ($this->type !== TypeEnum::Sale->value) {
            if (null !== $this->validFrom) {
                $context->buildViolation('shared.common.field_must_be_blank')
                    ->setParameter('field', 'validFrom')
                    ->atPath('validFrom')
                    ->addViolation();
            }
            if (null !== $this->validTo) {
                $context->buildViolation('shared.common.field_must_be_blank')
                    ->setParameter('field', 'validTo')
                    ->atPath('validTo')
                    ->addViolation();
            }
        }

        if ($this->validFrom && $this->validTo) {
            try {
                $from = new DateTimeImmutable($this->validFrom);
                $to = new DateTimeImmutable($this->validTo);
                if ($from > $to) {
                    $context->buildViolation('shared.datetime.before')
                        ->setParameter('before', 'validFrom')
                        ->setParameter('after', 'validTo')
                        ->atPath('validFrom')
                        ->addViolation();
                    $context->buildViolation('shared.datetime.after')
                        ->setParameter('after', 'validTo')
                        ->setParameter('before', 'validFrom')
                        ->atPath('validTo')
                        ->addViolation();
                }
            } catch (Throwable) {
            }
        }
    }

    /**
     * @return string[]
     */
    public static function getAvailableCurrencies(): array
    {
        return [
            CurrencyEnum::UAH->value,
            CurrencyEnum::USD->value,
        ];
    }

    /**
     * @return string[]
     */
    public static function getProductPriceTypes(): array
    {
        return [
            TypeEnum::Regular->value,
            TypeEnum::Sale->value,
            TypeEnum::Cost->value,
        ];
    }

    /**
     * @return string[]
     */
    public static function getProductPriceTaxTypes(): array
    {
        return [
            TaxTypeEnum::Percentage->value,
            TaxTypeEnum::Fixed->value,
        ];
    }
}
