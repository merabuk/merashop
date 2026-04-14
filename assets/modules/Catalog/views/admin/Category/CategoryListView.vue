<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <BaseBreadcrumbs :items="breadcrumbs" />
                    <p class="text-sm text-gray-500 mt-1">{{ t('catalog.categories.description') }}</p>
                </div>
                <a
                    :href="ADMIN_WEB_ENDPOINTS.CATEGORIES.ADD"
                    class="inline-flex items-center px-4 py-2.5 bg-merashop-500 hover:bg-emerald-600 text-white text-sm font-semibold rounded-lg shadow-sm transition-all active:scale-95"
                >
                    <span class="mr-2 text-lg">+</span>
                    {{ t('catalog.attributes.add') }}
                </a>
            </div>

            <div v-if="categoriesStore.error" class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg shadow-sm">
                <p class="font-bold">{{ t('common.errors.fail_load') }}</p>
                <p class="text-sm">{{ categoriesStore.error.message }}</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            {{ t('catalog.common.name') }}
                        </th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            {{ t('common.slug') }}
                        </th>
                        <th scope="col" class="relative px-6 py-4">
                            <span class="sr-only">{{ t('catalog.common.actions') }}</span>
                        </th>
                    </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                    <template v-if="categoriesStore.isLoading">
                        <tr v-for="i in 3" :key="i" class="animate-pulse">
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-3/4"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded w-1/2"></div></td>
                            <td class="px-6 py-4"><div class="h-6 bg-gray-200 rounded-full w-20"></div></td>
                            <td class="px-6 py-4 text-right"><div class="h-4 bg-gray-200 rounded w-10 ml-auto"></div></td>
                        </tr>
                    </template>

                    <template v-else-if="categoriesStore.categories.length > 0">
                        <tr v-for="category in categoriesStore.categories" :key="category.ulid" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ category.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ category.slug }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-3">
                                    <a
                                        :href="ADMIN_WEB_ENDPOINTS.CATEGORIES.EDIT(category.ulid)"
                                        class="text-gray-400 hover:text-merashop-500 transition-colors">✏️</a>
                                    <button class="text-gray-400 hover:text-red-500 transition-colors">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <tr v-else>
                        <td colspan="4" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="bg-gray-100 p-4 rounded-full mb-4">👕</div>
                                <p class="text-gray-500 text-lg">{{ t('catalog.categories.empty_list') }}</p>
                                <p class="text-gray-400 text-sm mt-1">{{ t('catalog.categories.empty_list_alter') }}</p>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@shared/layouts/admin/AdminLayout.vue';
import { ADMIN_WEB_ENDPOINTS } from "@shared/web/admin/endpoints.ts";
import { useCategoryStore } from "@catalog/store/useCategoryStore.ts";
import { BreadcrumbItem } from "@shared/types/admin/breadcrumb.interface.ts";
import { IconEnum } from "@shared/types/admin/icon.enum.ts";
import BaseBreadcrumbs from "@shared/components/admin/UI/BaseBreadcrumbs.vue";

const { t } = useI18n();
const categoriesStore = useCategoryStore();
const breadcrumbs: BreadcrumbItem[] = [
    { label: t('catalog.title'), icon: IconEnum.Catalog },
    { label: t('catalog.categories.title'), icon: IconEnum.Categories }
];

onMounted(() => {
    // categoriesStore.fetchItems();
});
</script>
