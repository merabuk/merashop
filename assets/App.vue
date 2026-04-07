<template>
    <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-6">
        <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full border-t-4 border-emerald-500">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">MeraShop</h1>
            <p class="text-sm text-gray-500 uppercase tracking-widest mb-6">Vue 3 + Symfony 7.4</p>

            <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100 mb-4">
                <p class="text-emerald-700 font-medium flex items-center">
                    <span class="mr-2">✅</span> Інфраструктура готова!
                </p>
            </div>

            <div class="space-y-2 text-left">
                <p class="text-xs text-gray-400 font-mono break-all">
                    <span class="font-bold text-gray-600">Trace ID:</span> {{ traceId }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    traceId: String
});
</script>
