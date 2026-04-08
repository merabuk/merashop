/**
 * Shared constants for the entire frontend application.
 * Mirrors the values defined in the PHP backend
 */
import { Locale, SUPPORTED_LOCALE_CODES } from '@shared/types/locale';

export const APP_NAME = 'MeraShop';

export const LOCALE = {
    COOKIE_NAME: '_locale',
    DEFAULT: Locale.UK,
    SUPPORTED: SUPPORTED_LOCALE_CODES,
} as const;

export const HTTP_HEADERS = {
    TRACE_ID: 'MeraShop-Trace-Id',
    ACCEPT_LANGUAGE: 'Accept-Language',
    REQUESTED_WITH: 'X-Requested-With',
} as const;

export const DOM_DATA_ATTRIBUTES = {
    TRACE_ID: 'traceId',
    USER: 'user',
    COMPONENT: 'component',
} as const;
