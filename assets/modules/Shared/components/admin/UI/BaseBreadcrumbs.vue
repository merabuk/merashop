<template>
    <nav class="flex mb-6 text-sm" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-y-0">
            <li class="inline-flex items-center">
                <a :href="ADMIN_WEB_ENDPOINTS.DASHBOARD"
                   class="text-gray-500 hover:text-merashop-500 transition-colors flex items-center">
                    <Icon :name="IconEnum.Dashboard" class="w-4 h-4 mr-1" />
                    {{ t('common.dashboard') }}
                </a>
            </li>

            <li v-for="(item, index) in items" :key="index" class="flex items-center">
                <span class="mx-2 text-gray-400">/</span>

                <a v-if="item.href && index !== items.length - 1"
                   :href="item.href"
                   class="text-gray-500 hover:text-merashop-500 transition-colors flex items-center">
                    <Icon v-if="item.icon" :name="item.icon" class="w-4 h-4 mr-1" />
                    {{ item.label }}
                </a>

                <span v-else
                      class="tracking-tight flex items-center"
                      :class="[index !== items.length - 1
                          ? 'text-gray-500'
                          : 'text-gray-900 font-bold'
                      ]"
                >
                    <Icon v-if="item.icon" :name="item.icon" class="w-4 h-4 mr-1" />
                    {{ item.label }}
                </span>
            </li>
        </ol>
    </nav>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import Icon from '@shared/components/admin/UI/Icon.vue';
import { ADMIN_WEB_ENDPOINTS } from '@shared/web/admin/endpoints';
import { BreadcrumbItem } from "@shared/types/admin/breadcrumb.interface";
import { IconEnum } from "@shared/types/admin/icon.enum";

defineProps<{
    items: BreadcrumbItem[]
}>();

const { t } = useI18n();
</script>
