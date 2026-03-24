<?php

declare(strict_types=1);

namespace App\Catalog\Domain\ValueObject\ProductAttributeValue\Value;

use App\Catalog\Domain\Exception\ProductAttributeValue\InvalidProductAttributeBaseLocalizedStringValueException;
use App\Shared\Domain\Enum\LocaleEnum;
use App\Shared\Domain\Exception\Services\Validation\InvalidStringException;
use App\Shared\Domain\Service\Validation\StringValidator;
use App\Shared\Domain\ValueObject\Contract\ValueObjectEqualityTrait;

abstract readonly class BaseLocalizedStringValue implements AttributeValueInterface
{
    use ValueObjectEqualityTrait;

    public const int MAX_LENGTH = 255;

    /**
     * @var array<string, string> [locale => value]
     */
    private array $translations;

    /**
     * @param array<string, string> $translations [locale => value]
     */
    public function __construct(array $translations)
    {
        $normalized = array_map(function (string $value) {
            return StringValidator::normalize($value);
        }, $translations);

        $this->translations = $normalized;
    }

    public function get(string|LocaleEnum $locale): ?string
    {
        $locale = $locale instanceof LocaleEnum ? $locale->value : $locale;

        return $this->translations[$locale] ?? null;
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
        $values = $this->translations;
        ksort($values);

        return $values;
    }

    /**
     * @param array<string, mixed> $data
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
            if (!is_string($value)) {
                throw InvalidProductAttributeBaseLocalizedStringValueException::becauseValueIsNotString($locale->value);
            }
            try {
                $translations[$locale->value] = StringValidator::validate(rawValue: $value, maxLength: static::MAX_LENGTH, normalize: false);
            } catch (InvalidStringException $e) {
                throw InvalidProductAttributeBaseLocalizedStringValueException::fromBaseException($e);
            }
        }

        return $translations;
    }
}
