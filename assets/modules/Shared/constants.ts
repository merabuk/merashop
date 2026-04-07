/**
 * Shared constants for the entire frontend application.
 * Mirrors the values defined in the PHP backend
 */

export const HTTP_HEADERS = {
    TRACE_ID: 'MeraShop-Trace-Id', // Must exactly match the PHP constant TRACE_ID_HEADER (HttpTraceIdListener)
    REQUESTED_WITH: 'X-Requested-With',
} as const;

export const DOM_DATA_ATTRIBUTES = {
    TRACE_ID: 'traceId',
    USER: 'user',
} as const;
