<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductPrice;

use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceValidFromException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceValidityPeriodException;
use App\Catalog\Domain\Exception\ProductPrice\InvalidProductPriceValidToException;
use App\Shared\Domain\ValueObject\Contract\EquatableInterface;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;
use DateTimeImmutable;
use Stringable;

final readonly class ValidityPeriod implements EquatableInterface, Stringable
{
    use ValueObjectEqualityTrait;

    /**
     * @throws InvalidProductPriceValidityPeriodException
     */
    public function __construct(
        private ValidFrom $from,
        private ValidTo $to,
    ) {
        if (
            $this->from->isAfter($this->to)
            || $this->from->equalsWithDateTime($this->to)
        ) {
            throw InvalidProductPriceValidityPeriodException::becauseItIsInvalidValidityPeriod('validFrom', 'validTo');
        }
    }

    /**
     * @throws InvalidProductPriceValidityPeriodException
     */
    public static function fromDateTimeRange(DateTimeImmutable $from, DateTimeImmutable $to): self
    {
        return new self(
            from: ValidFrom::fromDateTime($from),
            to: ValidTo::fromDateTime($to)
        );
    }

    /**
     * @throws InvalidProductPriceValidFromException
     * @throws InvalidProductPriceValidityPeriodException
     * @throws InvalidProductPriceValidToException
     */
    public static function fromStrings(string $from, string $to): self
    {
        return new self(
            from: ValidFrom::fromString($from),
            to: ValidTo::fromString($to)
        );
    }

    public function contains(DateTimeImmutable $now): bool
    {
        return ($this->from->isBefore($now) || $this->from->equalsWithDateTime($now))
            && ($this->to->isAfter($now) || $this->to->equalsWithDateTime($now));
    }

    public function getFrom(): ValidFrom
    {
        return $this->from;
    }

    public function getTo(): ValidTo
    {
        return $this->to;
    }

    public function __toString(): string
    {
        $from = $this->from->value()->format($this->from::OUTPUT_FORMAT);
        $to = $this->to->value()->format($this->to::OUTPUT_FORMAT);

        return $from.' - '.$to;
    }

    protected function getPrimitiveValue(): string
    {
        $from = $this->from->value()->format($this->from::COMPARISON_FORMAT);
        $to = $this->to->value()->format($this->to::COMPARISON_FORMAT);

        return $from.'_'.$to;
    }
}
