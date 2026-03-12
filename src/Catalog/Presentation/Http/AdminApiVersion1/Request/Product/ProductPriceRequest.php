<?php

declare(strict_types=1);

namespace App\Catalog\Presentation\Http\AdminApiVersion1\Request\Product;

use App\Catalog\Domain\Enum\ProductPrice\TypeEnum;
use App\Shared\Domain\Enum\TaxTypeEnum;
use DateTimeInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductPriceRequest
{
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public int $amount;

    #[Assert\NotBlank]
    #[Assert\Currency]
    public string $currency;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: 'getProductPriceTypes')]
    public string $type;

    #[Assert\NotBlank]
    #[Assert\Choice(callback: 'getProductPriceTaxTypes')]
    public string $taxType;

    #[Assert\NotBlank]
    #[Assert\Type('numeric')]
    #[Assert\Expression(
        expression: "
            (this.taxType == 'percentage' and value >= 0 and value <= 100)
            or (this.taxType == 'fixed' and value >= 0 and value <= this.amount)
        ",
        message: 'admin.api.v1.product.prices.tax.value.invalid'
    )]
    public float $taxValue;

    #[Assert\NotNull]
    #[Assert\Type('bool')]
    public bool $taxIncluded;

    #[Assert\Optional([
        new Assert\DateTime(DateTimeInterface::ATOM),
    ])]
    public ?string $validFrom = null;

    #[Assert\Optional([
        new Assert\DateTime(DateTimeInterface::ATOM),
    ])]
    public ?string $validTo = null;

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
