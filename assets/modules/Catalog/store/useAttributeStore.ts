import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useI18n } from "vue-i18n";
import axios, { type AxiosResponse } from 'axios';
import adminApiClient from '@shared/adminApiClient';
import { getActiveLocale } from '@shared/services/localeProvider';
import { LOCALE } from '@shared/constants';
import type { AttributeListItem, AttributeListResponse } from '@catalog/types/admin/attribute.interface';
import { ERROR_CODES, type ApiError } from '@shared/types/error';
import { CATALOG_ADMIN_API_ENDPOINTS } from '@catalog/api/admin/endpoints';

export const useAttributeStore = defineStore('catalog-attributes', () => {
    const { t } = useI18n();
    const attributes = ref<AttributeListItem[]>([]);
    const isLoading = ref(false);
    const error = ref<ApiError | null>(null);

    async function fetchAttributes() {
        isLoading.value = true;
        const locale = getActiveLocale();

        try {
            const response: AxiosResponse<AttributeListResponse[]> = await adminApiClient.get(
                CATALOG_ADMIN_API_ENDPOINTS.ATTRIBUTES.LIST
            );

            attributes.value = response.data.map((raw: AttributeListResponse): AttributeListItem => {
                return {
                    ulid: raw.ulid,
                    code: raw.code,
                    type: raw.type,
                    name: raw.translations[locale]?.name || raw.translations[LOCALE.DEFAULT]?.name || raw.code
                };
            });

        } catch (e: unknown) {
            if (axios.isAxiosError(e) && e.response) {
                error.value = e.response.data as ApiError;
            } else {
                error.value = {
                    errorCode: ERROR_CODES.UNEXPECTED_ERROR,
                    message: t('common.error.server_error')
                };
            }
        } finally {
            isLoading.value = false;
        }
    }

    return { attributes, isLoading, error, fetchAttributes };
});
