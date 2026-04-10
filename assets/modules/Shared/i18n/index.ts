import { createI18n } from 'vue-i18n';
import { getActiveLocale } from '@shared/services/locale-provider';
import { LOCALE } from '@shared/constants';
import { SUPPORTED_LOCALE_CODES, type SupportedLocaleCode } from '@shared/types/locale';

const moduleFiles = import.meta.glob<{ default: object }>('../../**/i18n/*.ts', { eager: true });

const buildMessages = () => {
    const allMessages: Record<string, object> = {};

    for (const path in moduleFiles) {
        const match = path.match(/\/([^/]+)\.ts$/);
        const fileName = match ? match[1] : null;

        if (!fileName || !SUPPORTED_LOCALE_CODES.includes(fileName as SupportedLocaleCode)) {
            continue;
        }

        if (!allMessages[fileName]) {
            allMessages[fileName] = {};
        }

        Object.assign(allMessages[fileName], moduleFiles[path].default);
    }

    return allMessages;
};

export const i18n = createI18n({
    legacy: false,
    locale: getActiveLocale(),
    fallbackLocale: LOCALE.DEFAULT,
    messages: buildMessages(),
});
