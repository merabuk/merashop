import { createI18n, type VueMessageType } from 'vue-i18n';
import type { LocaleMessage } from '@intlify/core-base';
import { getActiveLocale } from '@shared/services/localeProvider';
import { LOCALE } from '@shared/constants';
import { SUPPORTED_LOCALE_CODES, type SupportedLocaleCode } from '@shared/types/locale';

const moduleFiles = import.meta.glob<{ default: object }>('@/modules/**/i18n/*.ts', { eager: true });

const buildMessages = (): Record<SupportedLocaleCode, LocaleMessage<VueMessageType>> => {
    const allMessages = {} as Record<SupportedLocaleCode, LocaleMessage<VueMessageType>>;

    for (const path in moduleFiles) {
        const match = path.match(/\/([^/]+)\.ts$/);
        const fileName = match ? match[1] : null;

        if (!fileName) {
            continue;
        }

        const locale = fileName as SupportedLocaleCode;

        if (!SUPPORTED_LOCALE_CODES.includes(locale)) {
            continue;
        }

        if (!allMessages[locale]) {
            allMessages[locale] = {};
        }

        Object.assign(allMessages[locale], moduleFiles[path].default);
    }

    return allMessages;
};

export const i18n = createI18n({
    legacy: false,
    locale: getActiveLocale(),
    fallbackLocale: LOCALE.DEFAULT,
    messages: buildMessages(),
});
