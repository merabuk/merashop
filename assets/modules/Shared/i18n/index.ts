import { createI18n } from 'vue-i18n';
import { messages } from './messages';
import { getActiveLocale } from '@shared/services/locale-provider';
import { Locale } from '@shared/types/locale';

export const i18n = createI18n({
    legacy: false,
    locale: getActiveLocale(),
    fallbackLocale: Locale.EN,
    messages,
});
