export const ADMIN_WEB_ENDPOINTS = {
    DASHBOARD: '/admin/dashboard',
    ATTRIBUTES: {
        LIST: '/admin/catalog/attributes',
        ADD: '/admin/catalog/attributes/create',
        EDIT: (ulid: string) => `/admin/catalog/attributes/${ulid}/edit`,
    },
    CATEGORIES: {
        LIST: '/admin/catalog/categories',
        ADD: '/admin/catalog/categories/create',
        EDIT: (ulid: string) => `/admin/catalog/categories/${ulid}/edit`,
    },
    PRODUCTS: {
        LIST: '/admin/catalog/products',
        ADD: '/admin/catalog/products/create',
        EDIT: (ulid: string) => `/admin/catalog/products/${ulid}/edit`,
    },
    CUSTOMERS: {
        LIST: '/admin/customers',
    }
} as const;
