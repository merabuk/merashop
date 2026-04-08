<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Категорії товарів</h2>
                <button class="bg-merashop-500 text-white px-4 py-2 rounded-md hover:bg-emerald-600 transition">
                    Додати категорію
                </button>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-8 text-center text-gray-500">
                    Тут скоро з'явиться список категорій...
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/modules/Admin/layouts/AdminLayout.vue';
</script>
