export const PAGINATION_PARAMETERS = {
    LAST_SEEN_ID: 'lastSeenId',
    PER_PAGE: 'perPage',
} as const;

export const PER_PAGE_OPTIONS = [10, 25, 50] as const;

export const DEFAULT_PER_PAGE = PER_PAGE_OPTIONS[0] as number;

export const FILTER_PARAMETERS = {
    SEARCH: 'filters[search]',
} as const;

export const CUSTOM_FILTER_PARAMETER = (key: string) => `filters[${key}]`;

export const SORT_PARAMETERS = {
    FIELD: 'sortField',
    DIRECTION: 'sortDir',
    ASC: 'ASC',
    DESC: 'DESC',
} as const;

export type SortDirection = typeof SORT_PARAMETERS.ASC | typeof SORT_PARAMETERS.DESC;
