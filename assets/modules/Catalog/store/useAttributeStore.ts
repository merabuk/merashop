import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useI18n } from "vue-i18n";
import axios, { type AxiosResponse } from 'axios';
import adminApiClient from '@shared/adminApiClient';
import { getActiveLocale } from '@shared/services/localeProvider';
import { LOCALE } from '@shared/constants';
import type { AttributeListItem, AttributeListResponse } from '@catalog/types/admin/attribute.interface';
import type { AttributeSortField } from '@catalog/types/admin/attribute.constants';
import { ERROR_CODES, type ApiError } from '@shared/types/error';
import { CATALOG_ADMIN_API_ENDPOINTS } from '@catalog/api/admin/endpoints';
import { parsePaginationHeaders } from '@shared/services/paginationHeaderParser';
import {
    PAGINATION_PARAMETERS,
    DEFAULT_PER_PAGE,
    FILTER_PARAMETERS,
    SORT_PARAMETERS,
    type SortDirection
} from '@shared/types/pagination.constants.ts';

export const useAttributeStore = defineStore('catalog-attributes', () => {
    const { t } = useI18n();
    const attributes = ref<AttributeListItem[]>([]);
    const isLoading = ref(false);
    const isInitialLoading = ref(true);
    const error = ref<ApiError | null>(null);
    const nextCursor = ref<string | null>(null);
    const totalCount = ref(0);
    const hasMore = computed(() => nextCursor.value !== null);
    const searchQuery = ref('');
    const sortField = ref<AttributeSortField | null>(null);
    const sortDir = ref<SortDirection>(SORT_PARAMETERS.ASC);
    const perPage = ref(DEFAULT_PER_PAGE);

    async function fetchAttributes(isLoadMore = false) {
        if (isLoading.value) {
            return;
        }

        isLoading.value = true;
        error.value = null;

        const params = new URLSearchParams();

        if (isLoadMore && nextCursor.value) {
            params.append(PAGINATION_PARAMETERS.LAST_SEEN_ID, nextCursor.value);
        }
        params.append(PAGINATION_PARAMETERS.PER_PAGE, perPage.value.toString());

        if (sortField.value) {
            params.append(SORT_PARAMETERS.FIELD, sortField.value);
            params.append(SORT_PARAMETERS.DIRECTION, sortDir.value);
        }

        if (searchQuery.value.trim()) {
            params.append(FILTER_PARAMETERS.SEARCH, searchQuery.value.trim());
        }

        try {
            const response: AxiosResponse<AttributeListResponse[]> = await adminApiClient.get(
                `${CATALOG_ADMIN_API_ENDPOINTS.ATTRIBUTES.LIST}?${params.toString()}`
            );

            const meta = parsePaginationHeaders(response);
            const locale = getActiveLocale();

            const newItems = response.data.map((raw: AttributeListResponse): AttributeListItem => ({
                ulid: raw.ulid,
                code: raw.code,
                type: raw.type,
                name: raw.translations[locale]?.name || raw.translations[LOCALE.DEFAULT]?.name || raw.code
            }));

            if (isLoadMore) {
                attributes.value.push(...newItems);
            } else {
                attributes.value = newItems;
            }

            nextCursor.value = meta.nextCursor;
            totalCount.value = meta.totalCount;

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
            isInitialLoading.value = false;
        }
    }

    function toggleSort(field: AttributeSortField) {
        if (sortField.value === field) {
            sortDir.value = sortDir.value === SORT_PARAMETERS.ASC
                ? SORT_PARAMETERS.DESC
                : SORT_PARAMETERS.ASC;
        } else {
            sortField.value = field;
            sortDir.value = SORT_PARAMETERS.ASC;
        }
        reset();
        fetchAttributes();
    }

    function reset() {
        attributes.value = [];
        nextCursor.value = null;
        isInitialLoading.value = true;
    }

    function setPerPage(val: number) {
        perPage.value = val;
        reset();
        fetchAttributes();
    }

    function setSearchQuery(val: string) {
        searchQuery.value = val;
        reset();
        fetchAttributes();
    }

    return {
        attributes,
        isLoading,
        isInitialLoading,
        error,
        hasMore,
        totalCount,
        sortField,
        sortDir,
        setPerPage,
        setSearchQuery,
        toggleSort,
        fetchAttributes,
        reset
    };
});
