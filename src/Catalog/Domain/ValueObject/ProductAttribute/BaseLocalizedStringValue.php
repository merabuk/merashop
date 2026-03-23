<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttribute;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeBaseLocalizedStringValueException;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

abstract readonly class BaseLocalizedStringValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    public function __construct(
        /**
         * @var array<string, string> [locale => value]
         */
        private array $translations,
    ) {
    }

    /**
     * @return array<string, string> [locale => value]
     */
    public function value(): array
    {
        return $this->translations;
    }

    /**
     * @return array<string, string> [locale => value]
     */
    public function toArray(): array
    {
        return $this->translations;
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, string>
     */
    protected function getPrimitiveValue(): array
    {
        $values = array_map(static fn (array $t) => serialize($t), $this->translations);
        ksort($values);

        return $values;
    }

    /**
     * @param array<string, string> $data
     *
     * @return array<string, string>
     *
     * @throws InvalidProductAttributeBaseLocalizedStringValueException
     */
    protected static function mapAndEnsureIsValidValue(array $data): array
    {
        $translations = [];

        foreach (LocaleEnum::cases() as $locale) {
            $value = $data[$locale->value] ?? throw InvalidProductAttributeBaseLocalizedStringValueException::becauseLocaleIsMissing($locale->value);
            $value = is_string($value) ? mb_trim($value) : throw InvalidProductAttributeBaseLocalizedStringValueException::becauseValueIsNotString($locale->value);
            $translations[$locale->value] = $value;
        }

        return $translations;
    }
}
