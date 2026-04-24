import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import axios, { type AxiosResponse } from 'axios';
import adminApiClient from '@shared/api/adminApiClient';
import { ERROR_CODES, type ApiError } from '@shared/types/error';
import type { CategoryInterface } from '@catalog/types/admin/category.interface';
import { CATALOG_API_ENDPOINTS } from '@catalog/paths/admin/api';

export const useCategoryStore = defineStore('catalog-categories', () => {
    const { t } = useI18n();
    const categories = ref<CategoryInterface[]>([]);
    const isLoading = ref(false);
    const error = ref<ApiError | null>(null);

    async function fetchCategories() {
        isLoading.value = true;
        error.value = null;
        try {
            const response: AxiosResponse<CategoryInterface[]> = await adminApiClient.get(CATALOG_API_ENDPOINTS.CATEGORIES.LIST);
            categories.value = response.data;
        } catch (e: unknown) {
            if (axios.isAxiosError(e) && e.response) {
                error.value = e.response.data as ApiError;
            } else {
                error.value = {
                    errorCode: ERROR_CODES.UNEXPECTED_ERROR,
                    message: t('common.errors.server_error')
                };
            }
        } finally {
            isLoading.value = false;
        }
    }

    return { categories, isLoading, error, fetchCategories };
});
