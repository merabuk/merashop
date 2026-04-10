import { defineStore } from 'pinia';
import { ref } from 'vue';
import adminApiClient from '@shared/admin-api-client';
import { getActiveLocale } from '@shared/services/locale-provider';
import { LOCALE } from '@shared/constants';
import type { AttributeListItem, AttributeListResponse } from '@catalog/types/admin/attribute.interface';
import type { ApiError } from '@shared/types/error';
import axios, { type AxiosResponse } from 'axios';
import { CATALOG_ADMIN_API_ENDPOINTS } from '@catalog/api/admin/endpoints';

export const useAttributeStore = defineStore('catalog-attributes', () => {
    const items = ref<AttributeListItem[]>([]);
    const isLoading = ref(false);
    const error = ref<ApiError | null>(null);

    async function fetchItems() {
        isLoading.value = true;
        const locale = getActiveLocale();

        try {
            const response: AxiosResponse<AttributeListResponse[]> = await adminApiClient.get(
                CATALOG_ADMIN_API_ENDPOINTS.ATTRIBUTES.LIST
            );

            items.value = response.data.map((raw: AttributeListResponse): AttributeListItem => {
                return {
                    id: raw.id,
                    code: raw.code,
                    type: raw.type,
                    name: raw.translations[locale]?.name || raw.translations[LOCALE.DEFAULT]?.name || raw.code
                };
            });

        } catch (e: unknown) {
            if (axios.isAxiosError(e) && e.response) {
                error.value = e.response.data as ApiError;
            } else {
                error.value = { errorCode: 'UnexpectedError', message: 'An unexpected error occurred' };
            }
        } finally {
            isLoading.value = false;
        }
    }

    return { items, isLoading, error, fetchItems };
});
