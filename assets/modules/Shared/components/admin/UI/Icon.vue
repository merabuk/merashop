<template>
    <component :is="iconComponent" class="w-5 h-5" aria-hidden="true" />
</template>

<script setup lang="ts">
import { computed, FunctionalComponent } from 'vue';
import {
    TagIcon,
    Package,
    ChartBarStacked,
    ChevronDownIcon,
    ChevronUpIcon,
    Users,
    LayoutDashboard,
    HelpCircle,
    SquareChartGantt,
    Settings,
} from 'lucide-vue-next';
import { IconEnum } from "@shared/types/admin/icon.enum";

const ICON_MAP: Record<IconEnum, FunctionalComponent> = {
    [IconEnum.Attributes]: TagIcon,
    [IconEnum.Catalog]: Package,
    [IconEnum.Categories]: ChartBarStacked,
    [IconEnum.ChevronDown]: ChevronDownIcon,
    [IconEnum.ChevronUp]: ChevronUpIcon,
    [IconEnum.Customers]: Users,
    [IconEnum.Dashboard]: LayoutDashboard,
    [IconEnum.Default]: HelpCircle,
    [IconEnum.Products]: SquareChartGantt,
    [IconEnum.Settings]: Settings,
};

const props = defineProps<{
    name: IconEnum
}>();

const iconComponent = computed(() => {
    return ICON_MAP[props.name] || ICON_MAP[IconEnum.Default];
});
</script>
