/**
 * @see src/Catalog/Domain/ValueObject/Category
 */
export const CATEGORY_CONSTRAINTS = {
    SLUG: {
        MIN_LENGTH: 1,
        MAX_LENGTH: 255,
        ALLOWED_CHARS_REGEX: /^[a-z\d-]+$/,
        FULL_REGEX: /^(?![\d-])(?!.*--)[a-z\d-]+(?<!-)$/
    },
    NAME: {
        MIN_LENGTH: 1,
        MAX_LENGTH: 255
    },
    DESCRIPTION: {
        MIN_LENGTH: 0,
        MAX_LENGTH: 65_535
    },
} as const;

export const CATEGORY_SORT_FIELDS = {
    SLUG: 'slug',
    NAME: 'name'
} as const;

export type CategorySortField = typeof CATEGORY_SORT_FIELDS.SLUG | typeof CATEGORY_SORT_FIELDS.NAME;
