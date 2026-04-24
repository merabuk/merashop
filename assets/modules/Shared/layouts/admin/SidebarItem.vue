<template>
    <a :href="href"
       class="flex items-center px-6 py-3 transition border-l-4"
       :class="isActive
            ? 'bg-gray-800 border-merashop-500 text-white'
            : 'border-transparent text-gray-400 hover:bg-gray-800 hover:text-white'">
        <Icon v-if="icon" :name="icon" class="w-5 h-5" />
        <span class="ml-3">{{ label }}</span>
    </a>
</template>

<script setup lang="ts">
import Icon from '@shared/components/admin/UI/Icon.vue';
import type { IconEnum } from '@shared/types/admin/icon.enum';

defineProps<{ href: string, label: string, icon?: IconEnum, isActive: boolean }>();
</script>
