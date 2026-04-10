<template>
    <a :href="href"
       class="flex items-center pl-10 pr-6 py-2 text-sm transition group"
       :class="isActive
            ? 'text-merashop-500 font-medium bg-gray-900/50'
            : 'text-gray-500 hover:text-white hover:bg-gray-800/30'">

        <div class="w-4 h-4 mr-3 flex items-center justify-center"
             :class="isActive ? 'text-merashop-500' : 'text-gray-600 group-hover:text-gray-400'">
            <Icon v-if="icon" :name="icon" />
        </div>

        <span>{{ label }}</span>
    </a>
</template>

<script setup lang="ts">
    import Icon from '@shared/components/Icon.vue';
    
    defineProps<{
        href: string,
        label: string,
        isActive: boolean,
        icon?: string
    }>();
</script>
