import type { ComposerTranslation, NamedValue } from 'vue-i18n';

export const getPlaceholder = (t: ComposerTranslation, key: string, locale: string): string => {
    const named: NamedValue = {};

    return t(key, named, { locale } as Parameters<ComposerTranslation>[2]);
};
