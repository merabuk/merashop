<template>
    <div class="flex h-screen bg-gray-100 font-sans">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex-shrink-0">
            <div class="p-6 text-xl font-bold border-b border-gray-800">
                MeraShop <span class="text-merashop-500">Admin</span>
            </div>
            <nav class="mt-6 flex-1">
                <a href="/admin/dashboard" class="flex items-center px-6 py-3 hover:bg-gray-800 transition"
                   :class="{'bg-gray-800 border-l-4 border-merashop-500': currentComponent === 'AdminDashboard'}">
                    <span class="ml-3">{{ $t('common.dashboard') }}</span>
                </a>
                <div class="px-6 py-2 text-xs uppercase text-gray-500 font-bold mt-4">{{ $t('common.management') }}</div>
                <a href="/admin/catalog/categories" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-800 hover:text-white transition">
                    <span class="ml-3">{{ $t('common.catalog') }}</span>
                </a>
                <a href="/admin/catalog/products" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-800 hover:text-white transition">
                    <span class="ml-3">{{ $t('common.customers') }}</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 border-b">
                <div class="text-gray-500 text-sm italic">
                    Trace ID: <span class="font-mono text-xs">{{ session.traceId }}</span>
                </div>
                <div class="flex items-center space-x-4">
                    <LanguageSwitcher />
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-800">{{ session.userPayload?.sub || 'Admin' }}</p>
                        <p class="text-[10px] text-merashop-500 font-bold uppercase">{{ session.userPayload?.roles[0] }}</p>
                    </div>
                    <button @click="session.logout" class="text-gray-400 hover:text-red-500 transition">
                        {{ $t('common.logout') }}
                    </button>
                </div>
            </header>

            <!-- Scrollable Area -->
            <main class="flex-1 overflow-y-auto p-8">
                <slot /> <!-- Тут буде контент конкретної сторінки -->
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useSessionStore } from '@shared/store/useSessionStore';
import LanguageSwitcher from '@shared/components/LanguageSwitcher.vue';
const session = useSessionStore();

defineProps<{
    currentComponent?: string
}>();
</script>
