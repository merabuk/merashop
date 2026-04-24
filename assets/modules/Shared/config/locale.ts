import { Locale, SUPPORTED_LOCALE_CODES } from '@shared/types/locale';

export const LOCALE = {
    COOKIE_NAME: '_locale',
    DEFAULT: Locale.UK,
    SUPPORTED: SUPPORTED_LOCALE_CODES,
} as const;
