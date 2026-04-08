<template>
    <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-6">
        <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border-t-4 border-emerald-500">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">MeraShop</h1>

            <div v-if="session.isAuthenticated" class="mb-4 text-sm text-blue-600">
                Вітаємо, {{ session.userSession?.identifier }}!
            </div>

            <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100 mb-4">
                <p class="text-emerald-700 font-medium">✅ Стан через Pinia працює!</p>
            </div>

            <div class="text-left space-y-1">
                <p class="text-[10px] text-gray-400 font-mono">TRACE: {{ session.traceId }}</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useSessionStore } from '@shared/store/useSessionStore';

const session = useSessionStore();
</script>
