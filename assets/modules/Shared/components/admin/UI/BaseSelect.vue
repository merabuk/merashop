<template>
    <div class="w-full">
        <label v-if="label" :for="uuid" class="block text-sm font-bold text-gray-700 mb-1.5">
            {{ label }}
        </label>
        <div class="relative">
            <select
                :id="uuid"
                v-model="model"
                v-bind="$attrs"
                class="w-full px-4 py-2 border rounded-lg outline-none focus:outline-none transition-all duration-200 bg-white shadow-xs appearance-none cursor-pointer"
                :class="[error
                    ? 'border-red-500 focus:border-red-600 focus:ring-4 focus:ring-red-500/10'
                    : 'border-gray-300 focus:border-merashop-500 focus:ring-4 focus:ring-emerald-500/10'
                ]"
            >
                <slot />
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                </svg>
            </div>
        </div>
        <p v-if="error" class="text-red-500 text-[11px] mt-1.5 font-semibold leading-none">
            ⚠️ {{ error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { useId } from 'vue';

defineProps<{ label?: string, error?: string }>();

const model = defineModel<string | number>();
const uuid = `select-${useId()}`;
</script>
