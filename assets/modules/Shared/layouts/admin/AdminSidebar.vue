<template>
    <aside class="w-64 bg-gray-900 text-white flex-shrink-0 flex flex-col">
        <div class="p-6 text-xl font-bold border-b border-gray-800 shrink-0">
            MeraShop <span class="text-merashop-500">Admin</span>
        </div>

        <nav class="mt-4 flex-1 overflow-y-auto custom-scrollbar">
            <SidebarItem
                href="/admin/dashboard"
                icon="dashboard"
                :label="$t('common.dashboard')"
                :is-active="activeComponent === 'AdminDashboard'"
            />

            <div class="px-6 py-2 text-xs uppercase text-gray-500 font-bold mt-4">
                {{ $t('common.management') }}
            </div>

            <div class="space-y-1">
                <button
                    @click="isCatalogOpen = !isCatalogOpen"
                    class="w-full flex items-center justify-between px-6 py-3 text-gray-400 hover:bg-gray-800 hover:text-white transition group"
                    :class="{'text-white': isCatalogModule}"
                >
                    <div class="flex items-center">
                        <Icon name="catalog" class="w-5 h-5" />
                        <span class="ml-3">{{ $t('common.catalog') }}</span>
                    </div>
                    <Icon
                        name="chevron-down"
                        class="w-4 h-4 transition-transform duration-300"
                        :class="{'rotate-180': isCatalogOpen}"
                    />
                </button>

                <div v-show="isCatalogOpen" class="bg-gray-950/50 py-1 shadow-inner">
                    <SidebarSubItem
                        href="/admin/catalog/attributes"
                        icon="attributes"
                        :label="$t('catalog.attributes.title')"
                        :is-active="activeComponent === 'AttributeListView'"
                    />
                    <SidebarSubItem
                        href="/admin/catalog/categories"
                        icon="categories"
                        :label="$t('catalog.categories.title')"
                        :is-active="activeComponent === 'CategoryListView'"
                    />
                    <SidebarSubItem
                        href="/admin/catalog/products"
                        icon="products"
                        :label="$t('catalog.products.title')"
                        :is-active="activeComponent === 'ProductListView'"
                    />
                </div>
            </div>

            <SidebarItem
                href="/admin/customers"
                icon="customers"
                :label="$t('common.customers')"
                :is-active="activeComponent === 'CustomerListView'"
            />
        </nav>
    </aside>
</template>

<script setup lang="ts">
    import { ref, computed } from 'vue';
    import Icon from '@shared/components/Icon.vue';
    import SidebarItem from '@shared/layouts/admin/SidebarItem.vue';
    import SidebarSubItem from '@shared/layouts/admin/SidebarSubItem.vue';

    const props = defineProps<{
        activeComponent: string
    }>();

    const isCatalogModule = computed(() =>
        [
            'CategoryListView',
            'AttributeListView',
            'ProductListView',
        ].includes(props.activeComponent)
    );

    const isCatalogOpen = ref(isCatalogModule.value);
</script>
