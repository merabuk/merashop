import { AttributeType } from "@catalog/types/attribute.enum";

/**
 * @see src/Catalog/Domain/ValueObject/Attribute
 */
export const ATTRIBUTE_CONSTRAINTS = {
    CODE: {
        MIN_LENGTH: 1,
        MAX_LENGTH: 64,
        ALLOWED_CHARS_REGEX: /^[a-z\d-]+$/,
        FULL_REGEX: /^(?![-])(?!.*--)[a-z\d-]+(?<!-)$/
    },
    NAME: {
        MIN_LENGTH: 1,
        MAX_LENGTH: 255
    },
} as const;

export const ATTRIBUTE_TYPES_HAS_OPTIONS: readonly AttributeType[] = [
    AttributeType.Select,
    AttributeType.MultiSelect,
    AttributeType.Dimension,
] as const;

export const ATTRIBUTE_TYPES_ALLOWED_TO_CHANGE: (current: AttributeType) => Array<AttributeType> = (current) => {
    if (AttributeType.String === current || AttributeType.Text === current) {
        return [AttributeType.String, AttributeType.Text];
    }

    return [];
}
