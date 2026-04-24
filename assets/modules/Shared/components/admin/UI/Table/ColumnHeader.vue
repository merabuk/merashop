<template>
    <th
        scope="col"
        class="group px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
    >
        <div class="flex items-center gap-1">
            <span>{{ name }}</span>

            <span class="inline-flex w-3 h-3 items-center justify-center">
                <template v-if="showSort">
                    {{ sortDir === SORT_PARAMETERS.ASC ? '↑' : '↓' }}
                </template>
                <template v-else>
                    <span class="opacity-0 group-hover:opacity-20 transition-opacity">↑</span>
                </template>
            </span>
        </div>
    </th>
</template>

<script setup lang="ts">
import { SORT_PARAMETERS, SortDirection } from '@shared/types/admin/pagination.constants';

defineProps<{
    name: string,
    showSort: boolean,
    sortDir: SortDirection,
}>();
</script>
