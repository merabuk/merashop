<template>
    <aside class="w-64 bg-gray-900 text-white shrink-0 flex flex-col">
        <div class="p-6 text-xl font-bold border-b border-gray-800 shrink-0">
            MeraShop <span class="text-merashop-500">Admin</span>
        </div>

        <nav class="mt-4 flex-1 overflow-y-auto custom-scrollbar">
            <SidebarItem
                :href="ADMIN_WEB_ENDPOINTS.DASHBOARD"
                :icon="IconEnum.Dashboard"
                :label="t('common.dashboard')"
                :is-active="AdminSidebarView.Dashboard === activeComponent"
            />

            <div class="px-6 py-2 text-xs uppercase text-gray-500 font-bold mt-4">
                {{ t('common.management') }}
            </div>

            <div class="space-y-1">
                <button
                    @click="isCatalogOpen = !isCatalogOpen"
                    class="w-full flex items-center justify-between px-6 py-3 text-gray-400 hover:bg-gray-800 hover:text-white transition group"
                    :class="{'text-white': isCatalogModule}"
                >
                    <div class="flex items-center">
                        <Icon :name="IconEnum.Catalog" class="w-5 h-5" />
                        <span class="ml-3">{{ t('common.catalog') }}</span>
                    </div>
                    <Icon
                        :name="IconEnum.ChevronDown"
                        class="w-4 h-4 transition-transform duration-300"
                        :class="{'rotate-180': isCatalogOpen}"
                    />
                </button>

                <div v-show="isCatalogOpen" class="bg-gray-950/50 py-1 shadow-inner">
                    <SidebarSubItem
                        :href="ADMIN_WEB_ENDPOINTS.ATTRIBUTES.LIST"
                        :icon="IconEnum.Attributes"
                        :label="t('catalog.attributes.title')"
                        :is-active="AdminSidebarView.Attributes === activeComponent"
                    />
                    <SidebarSubItem
                        :href="ADMIN_WEB_ENDPOINTS.CATEGORIES.LIST"
                        :icon="IconEnum.Categories"
                        :label="t('catalog.categories.title')"
                        :is-active="AdminSidebarView.Categories === activeComponent"
                    />
                    <SidebarSubItem
                        :href="ADMIN_WEB_ENDPOINTS.PRODUCTS.LIST"
                        :icon="IconEnum.Products"
                        :label="t('catalog.products.title')"
                        :is-active="AdminSidebarView.Products === activeComponent"
                    />
                </div>
            </div>

            <SidebarItem
                :href="ADMIN_WEB_ENDPOINTS.CUSTOMERS.LIST"
                :icon="IconEnum.Customers"
                :label="t('common.customers')"
                :is-active="AdminSidebarView.Customers === activeComponent"
            />
        </nav>
    </aside>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@shared/components/admin/UI/Icon.vue';
import SidebarItem from '@shared/layouts/admin/SidebarItem.vue';
import SidebarSubItem from '@shared/layouts/admin/SidebarSubItem.vue';
import { ADMIN_WEB_ENDPOINTS } from '@shared/paths/admin/web';
import { IconEnum } from '@shared/types/admin/icon.enum';
import { AdminSidebarView, CATALOG_VIEWS } from '@shared/types/admin/sidebar.constants';

const { t } = useI18n();
const props = defineProps<{
    activeComponent: string
}>();

const isCatalogModule = computed(() => CATALOG_VIEWS.includes(props.activeComponent));

const isCatalogOpen = ref(isCatalogModule.value);
</script>
