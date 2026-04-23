export const CATALOG_API_ENDPOINTS = {
    ATTRIBUTES: {
        LIST: '/admin/api/v1/catalog/attributes',
        CREATE: '/admin/api/v1/catalog/attributes',
        GET: (ulid: string) => `/admin/api/v1/catalog/attributes/${ulid}`,
        UPDATE: (ulid: string) => `/admin/api/v1/catalog/attributes/${ulid}`,
        DELETE: (ulid: string) => `/admin/api/v1/catalog/attributes/${ulid}`,
    },
    CATEGORIES: {
        LIST: '/admin/api/v1/catalog/categories',
        CREATE: '/admin/api/v1/catalog/categories',
    }
} as const;
