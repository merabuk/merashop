export interface AttributeItemOptionItemResponse {
    ulid: string;
    code: string;
    translations: Record<string, OptionTranslation>;
    isActive: boolean;
    metadata: null | Record<string, string>;
}

export interface AttributeOption {
    ulid: string | null;
    code: string;
    isActive: boolean;
    baseRatio: null | number;
    translations: Record<string, OptionTranslation>;
}

export interface OptionTranslation {
    value: string;
}
