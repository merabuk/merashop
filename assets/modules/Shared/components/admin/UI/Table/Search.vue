<template>
    <div class="relative w-full md:w-96">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
            <span v-if="isLoading" class="animate-spin text-xs">🌀</span>
            <span v-else>🔍</span>
        </span>
        <input
            v-model="search"
            v-bind="$attrs"
            type="text"
            :placeholder="placeholder"
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-merashop-500 focus:border-merashop-500 sm:text-sm"
        />
    </div>
</template>

<script setup lang="ts">
const search = defineModel<string>({ default: '' });
defineProps<{
    isLoading: boolean,
    placeholder?: string
}>();
defineOptions({
    inheritAttrs: false
})
</script>
