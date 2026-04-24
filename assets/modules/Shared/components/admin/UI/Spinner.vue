<template>
    <div class="animate-spin inline-block w-8 h-8 border-4 border-merashop-500 border-t-transparent rounded-full mb-4"></div>
    <p class="text-gray-500">{{ t('common.loading') }}</p>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>
