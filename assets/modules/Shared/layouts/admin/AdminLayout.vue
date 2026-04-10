<template>
    <div class="flex h-screen bg-gray-100 font-sans overflow-hidden">
        <AdminSidebar :active-component="vueComponent" />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-8 border-b shrink-0 z-10">
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

            <main class="flex-1 overflow-y-auto p-8 bg-gray-50/50">
                <slot />
            </main>
        </div>

        <ToastContainer />
    </div>
</template>

<script setup lang="ts">
    import { computed } from 'vue';
    import { useSessionStore } from '@shared/store/useSessionStore';
    import LanguageSwitcher from '@shared/components/LanguageSwitcher.vue';
    import AdminSidebar from '@shared/layouts/admin/AdminSidebar.vue';
    import ToastContainer from '@shared/components/ToastContainer.vue';
    
    const session = useSessionStore();
    
    defineProps<{
        currentComponent?: string
    }>();
    
    const vueComponent = computed(() => document.getElementById('app')?.dataset.component || 'App');
</script>
