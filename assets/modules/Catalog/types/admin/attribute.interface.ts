import { AttributeType } from "@catalog/types/attribute.enum";
import { AttributeItemOptionItemResponse } from "@catalog/types/admin/option.interface";

export interface AttributeListResponse {
    ulid: string;
    code: string;
    type: AttributeType;
    translations: Record<string, { name: string }>;
    version: number;
}

export interface AttributeListItem {
    ulid: string;
    name: string;
    code: string;
    type: AttributeType;
}

export interface AttributeItemResponse {
    ulid: string;
    code: string;
    type: AttributeType;
    translations: Record<string, AttributeTranslation>;
    options: Record<string, AttributeItemOptionItemResponse>;
    version: number;
}

export interface AttributeCreateResponse {
    message: string;
}
export interface AttributeUpdateResponse {
    message: string;
}

export interface AttributeTranslation {
    name: string;
}


