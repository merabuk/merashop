<template>
    <div class="fixed bottom-5 right-5 z-[100] flex flex-col gap-3 w-full max-w-sm">
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform translate-x-10 opacity-0"
            enter-to-class="transform translate-x-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <div
                v-for="toast in toastStore.toasts"
                :key="toast.id"
                class="flex items-center p-4 rounded-lg shadow-lg border-l-4 bg-white"
                :class="getTypeClasses(toast.type)"
            >
                <div class="flex-1 text-sm font-medium">
                    {{ toast.message }}
                </div>
                <button @click="toastStore.remove(toast.id)" class="ml-4 text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup lang="ts">
import { useToastStore, ToastType } from '@shared/stores/admin/useToastStore';

const toastStore = useToastStore();

const getTypeClasses = (type: ToastType) => {
    switch (type) {
        case ToastType.Success: return 'border-emerald-500 text-emerald-800';
        case ToastType.Error: return 'border-red-500 text-red-800';
        case ToastType.Warning: return 'border-amber-500 text-amber-800';
        default: return 'border-blue-500 text-blue-800';
    }
};
</script>
