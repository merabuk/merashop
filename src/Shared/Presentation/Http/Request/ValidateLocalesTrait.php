<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http\Request;

use App\Shared\Domain\Enum\LocaleEnum;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ValidateLocalesTrait
{
    #[Assert\Callback]
    public function _validateLocales(ExecutionContextInterface $context): void
    {
        $translations = $this->getTranslations();
        if (empty($translations)) {
            return;
        }

        $validLocales = $this->getValidLocales();
        $providedLocales = array_keys($translations);

        $missingLocales = array_diff($validLocales, $providedLocales);
        if (!empty($missingLocales)) {
            $context->buildViolation($this->getMissingTranslationKey())
                ->setParameter('locales', implode(', ', $missingLocales))
                ->atPath($this->getRequestTranslationKey())
                ->addViolation();
        }

        $invalid = array_diff($providedLocales, $validLocales);
        foreach ($invalid as $locale) {
            $context->buildViolation($this->getInvalidTranslationLocaleKey())
                ->setParameter('locale', (string) $locale)
                ->atPath(sprintf('%s[%s]', $this->getRequestTranslationKey(), (string) $locale))
                ->addViolation();
        }
    }

    /**
     * @return string[]
     */
    protected function getValidLocales(): array
    {
        return LocaleEnum::getValues();
    }

    abstract protected function getTranslations(): array;

    abstract protected function getRequestTranslationKey(): string;

    protected function getMissingTranslationKey(): string
    {
        return 'shared.common.translations_missing';
    }

    protected function getInvalidTranslationLocaleKey(): string
    {
        return 'shared.common.translations_locale_invalid';
    }
}
