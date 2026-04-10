import { defineStore } from 'pinia';
import { ref } from 'vue';
import adminApiClient from '@shared/admin-api-client';
import type { CategoryInterface } from '@catalog/types/admin/category.interface';
import type { ApiError } from '@shared/types/error';
import axios, { type AxiosResponse } from 'axios';
import { CATALOG_ADMIN_API_ENDPOINTS } from '@catalog/api/admin/endpoints';

export const useCategoryStore = defineStore('catalog-categories', () => {
    const categories = ref<CategoryInterface[]>([]);
    const isLoading = ref(false);
    const error = ref<ApiError | null>(null);

    async function fetchCategories() {
        isLoading.value = true;
        error.value = null;
        try {
            const response: AxiosResponse<CategoryInterface[]> = await adminApiClient.get(CATALOG_ADMIN_API_ENDPOINTS.CATEGORIES.LIST);
            categories.value = response.data;
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

    return { categories, isLoading, error, fetchCategories };
});
