<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">
                    {{ $t('catalog.categories.title') }}
                </h2>
                <button class="bg-merashop-500 text-white px-4 py-2 rounded-md hover:bg-emerald-600 transition">
                    {{ $t('catalog.categories.add') }}
                </button>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden p-8 text-center text-gray-500">
                {{ $t('catalog.categories.empty_list') }}
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@shared/layouts/admin/AdminLayout.vue';
</script>
