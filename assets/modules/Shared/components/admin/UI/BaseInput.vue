<template>
    <div class="w-full">
        <label v-if="label" :for="uuid" class="flex justify-between items-end text-sm font-bold text-gray-700 mb-1.5">
            <span>{{ label }}</span>
            <span v-if="maxLength"
                  :class="[model.length >= maxLength ? 'text-red-500' : 'text-gray-400']"
                  class="text-[10px] font-mono font-normal uppercase tracking-tighter">
                {{ model.length }} / {{ maxLength }}
            </span>
        </label>

        <div class="relative">
            <input
                :id="uuid"
                v-model="model"
                @input="filterInput"
                v-bind="$attrs"
                :maxlength="maxLength"
                class="w-full px-4 py-2 border rounded-lg transition-all duration-200 bg-white shadow-xs outline-none focus:outline-none"
                :class="[error
                    ? 'border-red-500 focus:border-red-600 focus:ring-4 focus:ring-red-500/10'
                    : 'border-gray-300 focus:border-merashop-500 focus:ring-4 focus:ring-emerald-500/10'
                ]"
            />
        </div>

        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0 -translate-y-1"
            enter-to-class="opacity-100 translate-y-0">
            <p v-if="error" class="text-red-500 text-[11px] mt-1.5 font-semibold leading-none flex items-center">
                <span class="mr-1">⚠️</span> {{ error }}
            </p>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { useId } from 'vue';

const props = defineProps<{
    label?: string;
    error?: string;
    maxLength?: number;
    allowedPattern?: RegExp;
}>();

const model = defineModel<string>({ default: '' });
const uuid = `input-${useId()}`;

const filterInput = (event: Event) => {
    if (!props.allowedPattern) {
        return;
    }

    const input = event.target as HTMLInputElement;
    const filtered = input.value.split('')
        .filter(char => props.allowedPattern?.test(char))
        .join('');

    if (filtered !== input.value) {
        model.value = filtered;
        input.value = filtered;
    }
};
</script>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>
