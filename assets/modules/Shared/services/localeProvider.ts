import { LOCALE } from '@shared/config/locale';

export const getActiveLocale = (): string => {
    const htmlLang = document.documentElement.lang;

    if (htmlLang && (LOCALE.SUPPORTED as readonly string[]).includes(htmlLang)) {
        return htmlLang;
    }

    return LOCALE.DEFAULT;
};
