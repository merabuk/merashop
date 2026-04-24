export enum Locale {
    UK = 'uk',
    EN = 'en'
}

export const SUPPORTED_LOCALE_CODES = [Locale.UK, Locale.EN] as const;

export type SupportedLocaleCode = typeof SUPPORTED_LOCALE_CODES[number];

export const SUPPORTED_LOCALES = [
    { code: Locale.UK, name: 'Українська', flag: '🇺🇦' },
    { code: Locale.EN, name: 'English', flag: '🇺🇸' }
];
