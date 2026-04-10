/**
 * @see src/Catalog/Domain/ValueObject/AttributeOption
 */
export const OPTION_CONSTRAINTS = {
    CODE: {
        MIN_LENGTH: 1,
        MAX_LENGTH: 64,
        ALLOWED_CHARS_REGEX: /^[a-z\d-]+$/,
        FULL_REGEX: /^(?![-])(?!.*--)[a-z\d-]+(?<!-)$/
    },
    VALUE: {
        MIN_LENGTH: 1,
        MAX_LENGTH: 255
    },
} as const;
