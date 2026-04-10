<template>
    <div class="w-full">
        <label v-if="label" :for="uuid" class="flex justify-between items-end text-sm font-bold text-gray-700 mb-1.5">
            <span>{{ label }}</span>
            <span v-if="maxLength"
                  :class="[modelValue.length >= maxLength ? 'text-red-500' : 'text-gray-400']"
                  class="text-[10px] font-mono font-normal uppercase tracking-tighter">
                {{ modelValue.length }} / {{ maxLength }}
            </span>
        </label>

        <div class="relative">
            <input
                :id="uuid"
                :value="modelValue"
                @input="handleInput"
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
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform -translate-y-1 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
        >
            <p v-if="error" class="text-red-500 text-[11px] mt-1.5 font-semibold flex items-center leading-none">
                <span class="mr-1">⚠️</span> {{ error }}
            </p>
        </Transition>
    </div>
</template>

<script setup lang="ts">
    import { ref } from 'vue';
    
    const props = defineProps<{
        modelValue: string;
        label?: string;
        error?: string;
        maxLength?: number;
        allowedPattern?: RegExp;
    }>();
    
    const emit = defineEmits(['update:modelValue']);
    
    const uuid = ref(`input-${Math.random().toString(36).slice(2, 9)}`);
    
    const handleInput = (event: Event) => {
        const input = event.target as HTMLInputElement;
        let value = input.value;
    
        if (props.allowedPattern) {
            const filteredValue = value.split('')
                .filter(char => props.allowedPattern?.test(char))
                .join('');
    
            if (filteredValue !== value) {
                value = filteredValue;
                input.value = value;
            }
        }
    
        emit('update:modelValue', value);
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
