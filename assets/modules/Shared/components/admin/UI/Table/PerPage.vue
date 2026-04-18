<template>
    <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500">{{ t('common.pagination.per_page') }}:</span>
        <select
            :id="uuid"
            v-model="model"
            v-bind="$attrs"
            class="block w-20 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-merashop-500 focus:border-merashop-500 sm:text-sm rounded-lg"
        >
            <option v-for="option in PER_PAGE_OPTIONS" :key="option" :value="option">{{ option }}</option>
        </select>
    </div>
</template>

<script setup lang="ts">
import { useId } from 'vue';
import { useI18n } from 'vue-i18n';
import { PER_PAGE_OPTIONS, DEFAULT_PER_PAGE } from '@shared/types/pagination.constants.ts';

const { t } = useI18n();
const uuid = `select-${useId()}`;
const model = defineModel<number>({ default: DEFAULT_PER_PAGE });
defineOptions({
    inheritAttrs: false
})
</script>
