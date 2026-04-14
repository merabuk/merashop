<template>
    <button
        :type="type"
        :disabled="disabled || isLoading"
        class="inline-flex items-center justify-center px-6 py-2.5 rounded-lg font-bold text-sm transition-all duration-200 shadow-sm active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
        :class="variantClasses"
    >
        <svg v-if="isLoading" class="animate-spin -ml-1 mr-3 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>

        <span v-if="$slots.icon && !isLoading" class="mr-2 flex items-center justify-center w-4 h-4">
            <slot name="icon" />
        </span>

        <slot />
    </button>
</template>

<script setup lang="ts">
import { computed, useSlots } from 'vue';

interface Props {
    type?: 'button' | 'submit' | 'reset';
    isLoading?: boolean;
    disabled?: boolean;
    variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
}

const props = withDefaults(defineProps<Props>(), {
    type: 'button',
    isLoading: false,
    disabled: false,
    variant: 'primary'
});

const $slots = useSlots();

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200';
        case 'danger':
            return 'bg-red-500 text-white hover:bg-red-600 focus:ring-4 focus:ring-red-100';
        case 'ghost':
            return 'bg-transparent text-gray-500 hover:bg-gray-100 shadow-none';
        case 'primary':
        default:
            return 'bg-merashop-500 text-white hover:bg-emerald-600 focus:ring-4 focus:ring-emerald-100';
    }
});
</script>
