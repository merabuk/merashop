<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <BaseBreadcrumbs :items="breadcrumbs" />
                    <p class="text-sm text-gray-500 mt-1">{{ t('catalog.attributes.description') }}</p>
                </div>
                <a
                    :href="ADMIN_WEB_ENDPOINTS.ATTRIBUTES.ADD"
                    class="inline-flex items-center px-4 py-2.5 bg-merashop-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg shadow-sm transition-all active:scale-95"
                >
                    <span class="mr-2 text-lg">+</span>
                    {{ t('catalog.attributes.add') }}
                </a>
            </div>

            <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <Search
                    v-model="localSearch"
                    @update:model-value="debouncedSetSearch"
                    :is-loading="attributeStore.isLoading"
                    :placeholder="t('catalog.attributes.search_placeholder')"
                />

                <PerPage
                    v-model="localPerPage"
                    @update:model-value="attributeStore.setPerPage"
                />
            </div>

            <div v-if="attributeStore.error" class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg shadow-sm">
                <p class="font-bold">{{ t('common.errors.fail_load') }}</p>
                <p class="text-sm">{{ attributeStore.error.message }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                <table class="min-w-200 divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <ColumnHeader
                            class="w-3/6"
                            @click="attributeStore.toggleSort(ATTRIBUTE_SORT_FIELDS.NAME)"
                            :name="t('catalog.common.name')"
                            :show-sort="attributeStore.sortField === ATTRIBUTE_SORT_FIELDS.NAME"
                            :sort-dir="attributeStore.sortDir"
                        />
                        <ColumnHeader
                            class="w-2/6"
                            @click="attributeStore.toggleSort(ATTRIBUTE_SORT_FIELDS.CODE)"
                            :name="t('catalog.common.code')"
                            :show-sort="attributeStore.sortField === ATTRIBUTE_SORT_FIELDS.CODE"
                            :sort-dir="attributeStore.sortDir"
                        />
                        <ColumnHeader
                            class="w-1/6"
                            @click="attributeStore.toggleSort(ATTRIBUTE_SORT_FIELDS.TYPE)"
                            :name="t('catalog.common.type')"
                            :show-sort="attributeStore.sortField === ATTRIBUTE_SORT_FIELDS.TYPE"
                            :sort-dir="attributeStore.sortDir"
                        />
                        <th scope="col" class="relative px-6 py-4">
                            <span class="sr-only">{{ t('common.actions') }}</span>
                        </th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                    <template v-if="attributeStore.isInitialLoading">
                        <tr v-for="i in localPerPage" :key="i" class="animate-pulse">
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-3/6"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-2/6"></div></td>
                            <td class="px-6 py-4"><div class="h-6 bg-gray-200 rounded-full w-1/6"></div></td>
                            <td class="px-6 py-4 text-right"><div class="h-4 bg-gray-200 rounded w-10 ml-auto"></div></td>
                        </tr>
                    </template>

                    <template v-else-if="attributeStore.attributes.length > 0">
                        <tr v-for="attribute in attributeStore.attributes" :key="attribute.ulid" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ attribute.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded font-mono text-xs border border-gray-200">
                                    {{ attribute.code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getTypeClass(attribute.type)" class="px-2 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wide">
                                    {{ attribute.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-3">
                                    <a
                                        :href="ADMIN_WEB_ENDPOINTS.ATTRIBUTES.EDIT(attribute.ulid)"
                                        class="text-gray-400 hover:text-merashop-500 transition-colors">✏️</a>
                                    <button class="text-gray-400 hover:text-red-500 transition-colors">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr v-else>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="bg-gray-100 p-4 rounded-full mb-4">🏷️</div>
                                <p class="text-gray-500 text-lg">{{ t('catalog.attributes.empty_list') }}</p>
                                <p class="text-gray-400 text-sm mt-1">{{ t('catalog.attributes.empty_list_alter') }}</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <LoadMore
                    v-if="attributeStore.hasMore"
                    @click="attributeStore.fetchAttributes(true)"
                    :is-loading="attributeStore.isLoading"
                    :text="t('common.pagination.load_more')"
                    :value="`${attributeStore.attributes.length} / ${attributeStore.totalCount}`"
                />
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { debounce } from '@shared/services/debounce';
import AdminLayout from '@shared/layouts/admin/AdminLayout.vue';
import BaseBreadcrumbs from "@shared/components/admin/UI/BaseBreadcrumbs.vue";
import ColumnHeader from "@shared/components/admin/UI/Table/ColumnHeader.vue";
import LoadMore from "@shared/components/admin/UI/Table/LoadMore.vue";
import PerPage from "@shared/components/admin/UI/Table/PerPage.vue";
import Search from "@shared/components/admin/UI/Table/Search.vue";
import { useAttributeStore } from '@catalog/store/useAttributeStore';
import { ADMIN_WEB_ENDPOINTS } from "@shared/web/admin/endpoints";
import { AttributeType } from "@catalog/types/attribute.enum";
import { ATTRIBUTE_SORT_FIELDS } from '@catalog/types/admin/attribute.constants';
import { BreadcrumbItem } from "@shared/types/admin/breadcrumb.interface.ts";
import { IconEnum } from "@shared/types/admin/icon.enum.ts";
import { DEFAULT_PER_PAGE } from '@shared/types/pagination.constants.ts';

const { t } = useI18n();
const attributeStore = useAttributeStore();
const breadcrumbs: BreadcrumbItem[] = [
    { label: t('catalog.title'), icon: IconEnum.Catalog },
    { label: t('catalog.attributes.title'), icon: IconEnum.Attributes }
];

const localSearch = ref('');
const debouncedSetSearch = debounce(attributeStore.setSearchQuery)

const localPerPage = ref(DEFAULT_PER_PAGE);

onMounted(() => {
    attributeStore.fetchAttributes();
});

const TYPE_COLORS: Record<AttributeType, string> = {
    [AttributeType.String]:      'bg-blue-50 text-blue-700 border-blue-200',
    [AttributeType.Text]:        'bg-blue-100 text-blue-800 border-blue-300',

    [AttributeType.Integer]:     'bg-purple-50 text-purple-700 border-purple-200',
    [AttributeType.Float]:       'bg-indigo-50 text-indigo-700 border-indigo-200',

    [AttributeType.Boolean]:     'bg-amber-50 text-amber-700 border-amber-200',

    [AttributeType.Select]:      'bg-emerald-50 text-emerald-700 border-emerald-200',
    [AttributeType.MultiSelect]: 'bg-emerald-100 text-emerald-800 border-emerald-300',

    [AttributeType.Color]:       'bg-rose-50 text-rose-700 border-rose-200',
    [AttributeType.Date]:        'bg-teal-50 text-teal-700 border-teal-200',
    [AttributeType.Url]:         'bg-sky-50 text-sky-700 border-sky-200',

    [AttributeType.Dimension]:   'bg-orange-50 text-orange-700 border-orange-200',
};

const getTypeClass = (type: AttributeType): string => {
    return TYPE_COLORS[type] || 'bg-gray-50 text-gray-700 border-gray-200';
};
</script>
