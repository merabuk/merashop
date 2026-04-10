<template>
    <component :is="iconComponent" class="w-5 h-5" />
</template>

<script setup lang="ts">
    import {
        ChartBarStacked,
        ChevronDownIcon,
        ChevronUpIcon,
        LayoutDashboard,
        Package,
        HelpCircle,
        Settings,
        SquareChartGantt,
        TagIcon,
        Users,
    } from 'lucide-vue-next';
    import {FunctionalComponent} from "vue";

    const props = defineProps<{
        name: string
    }>();


    const iconMap: Record<string, FunctionalComponent> = {
        'attributes': TagIcon,
        'catalog': Package,
        'categories': ChartBarStacked,
        'chevron-down': ChevronDownIcon,
        'chevron-up': ChevronUpIcon,
        'customers': Users,
        'dashboard': LayoutDashboard,
        'default': HelpCircle,
        'products': SquareChartGantt,
        'settings': Settings
    };

    const iconComponent = iconMap[props.name] || iconMap['default'];
</script>
