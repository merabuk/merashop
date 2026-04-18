<template>
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-center">
        <button
            v-bind="$attrs"
            :disabled="isLoading"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-merashop-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
        >
            <span v-if="isLoading" class="mr-2 animate-spin">⏳</span>
            <span>{{ text }}</span>
            <span class="ml-2 text-gray-400">{{ value }}</span>
        </button>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    isLoading: boolean,
    text: string,
    value: string,
}>();
defineOptions({
    inheritAttrs: false
})
</script>

