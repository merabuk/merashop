import { AttributeType } from "@catalog/types/attribute.enum";

export interface AttributeListResponse {
    id: number;
    code: string;
    type: AttributeType;
    translations: Record<string, { name: string }>;
    version: number;
}

export interface AttributeListItem {
    id: string | number;
    name: string;
    code: string;
    type: AttributeType;
}

export interface AttributeCreateResponse {
    message: string;
}
