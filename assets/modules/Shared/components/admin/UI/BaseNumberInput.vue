<template>
    <div class="w-full">
        <label v-if="label" :for="uuid" class="flex justify-between items-end text-sm font-bold text-gray-700 mb-1.5">
            {{ label }}
        </label>
        <div class="relative">
            <input
                :id="uuid"
                type="number"
                v-model="model"
                v-bind="$attrs"
                class="w-full px-4 py-2 border rounded-lg outline-none focus:outline-none transition-all duration-200 bg-white shadow-xs"
                :class="[error
                    ? 'border-red-500 focus:border-red-600 focus:ring-4 focus:ring-red-500/10'
                    : 'border-gray-300 focus:border-merashop-500 focus:ring-4 focus:ring-emerald-500/10'
                ]"
            />
        </div>
        <p v-if="error" class="text-red-500 text-[11px] mt-1.5 font-semibold leading-none">
            ⚠️ {{ error }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { useId } from 'vue';

defineProps<{ label?: string, error?: string }>();

const model = defineModel<number | null>({ default: 1 });
const uuid = `input-${useId()}`;
</script>
