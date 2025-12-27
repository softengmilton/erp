<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Period Filter -->
            <div class="flex justify-end">
                <select v-model="selectedPeriod" class="rounded-lg border bg-white px-6 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="all">All</option>
                    <option value="today">Today</option>
                    <option value="month">This Month</option>
                    <option value="year">This Year</option>
                </select>
            </div>

            <!-- First row: horizontally scrollable widgets -->
            <div class="flex gap-4 overflow-x-auto p-4">
                <Widget
                    v-for="(item, index) in firstRowWidgets"
                    :key="index"
                    :title="item.title"
                    :gradientFrom="item.gradientFrom"
                    :gradientTo="item.gradientTo"
                    class="min-w-[140px] flex-shrink-0 cursor-pointer transition-all duration-200"
                    :class="{
                        'scale-105 transform ring-4 ring-indigo-500 ring-offset-2': selectedIndex === index,
                    }"
                    @click="selectedIndex = index"
                >
                    <template #icon>
                        <component :is="item.icon" class="h-8 w-8" />
                    </template>
                </Widget>
            </div>

            <!-- Second row: Stats -->
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                <Widget
                    v-for="(item, index) in secondRowWidgets"
                    :key="index"
                    :title="item.title"
                    :value="item.value"
                    :gradientFrom="item.gradientFrom"
                    :gradientTo="item.gradientTo"
                    class="min-h-[80px] p-2 text-sm sm:p-3 sm:text-base"
                />
            </div>

            <!-- Third row: Charts -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <SpilineAreaChart :chartData="monthlyData" />
                </div>

                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <StackedColumnsChart :chartData="salesByType" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
/**
 * DASHBOARD PAGE
 * - Today / Month / Year filter
 * - Inertia reload
 * - Reactive widgets
 */

import Widget from '@/components/Widget.vue';
import SpilineAreaChart from '@/components/charts/SpilineAreaChart.vue';
import StackedColumnsChart from '@/components/charts/StackedColumnsChart.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber } from '@/utils/helper';
import { Head, router } from '@inertiajs/vue3';
import * as LucideIcons from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

/* ---------------- PROPS ---------------- */
const props = defineProps({
    widgets: { type: Object, default: () => ({}) },
    monthlyData: {
        type: Object,
        default: () => ({ months: [], revenue: [], expenses: [] }),
    },
    salesByType: { type: Object, default: () => ({ months: [], data: [] }) },
});

/* ---------------- STATE ---------------- */
const breadcrumbs = [{ title: 'Dashboard', href: '/dashboard' }];
const selectedIndex = ref(0);
const selectedPeriod = ref<'all' | 'today' | 'month' | 'year'>('month');

/* ---------------- INERTIA RELOAD ---------------- */
watch(selectedPeriod, (period) => {
    router.get(
        '/dashboard',
        { period },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
});

/* ---------------- FIRST ROW ---------------- */
const firstRowWidgets = [
    { title: 'Store', gradientFrom: 'from-pink-500', gradientTo: 'to-red-400', icon: LucideIcons.Store },
    { title: 'Restaurant', gradientFrom: 'from-green-400', gradientTo: 'to-teal-400', icon: LucideIcons.Coffee },
    { title: 'War House', gradientFrom: 'from-pink-400', gradientTo: 'to-red-400', icon: LucideIcons.Home },
    { title: 'Convention Hall', gradientFrom: 'from-yellow-400', gradientTo: 'to-orange-400', icon: LucideIcons.Home },
    { title: 'Park', gradientFrom: 'from-blue-500', gradientTo: 'to-indigo-400', icon: LucideIcons.ShoppingCart },
    { title: 'Resort', gradientFrom: 'from-purple-500', gradientTo: 'to-pink-400', icon: LucideIcons.Home },
    { title: 'Swimming Pool', gradientFrom: 'from-cyan-400', gradientTo: 'to-blue-400', icon: LucideIcons.Coffee },
    { title: 'Ride', gradientFrom: 'from-pink-500', gradientTo: 'to-red-400', icon: LucideIcons.ShoppingCart },
    { title: 'Children Zone', gradientFrom: 'from-blue-500', gradientTo: 'to-indigo-400', icon: LucideIcons.Home },
    { title: 'Boat Ride', gradientFrom: 'from-yellow-400', gradientTo: 'to-orange-400', icon: LucideIcons.ShoppingCart },
    { title: 'LPG Station', gradientFrom: 'from-pink-500', gradientTo: 'to-red-400', icon: LucideIcons.Home },
];

/* ---------------- SECOND ROW (REACTIVE) ---------------- */
const secondRowWidgets = computed(() => [
    {
        title: 'Sales',
        value: formatNumber(props.widgets.sales?.[selectedPeriod.value] || 0),
        gradientFrom: 'from-blue-500',
        gradientTo: 'to-indigo-400',
    },
    {
        title: 'Revenue',
        value: formatNumber(props.widgets.revenue?.[selectedPeriod.value] || 0),
        gradientFrom: 'from-pink-500',
        gradientTo: 'to-red-400',
    },
    {
        title: 'Products',
        value: props.widgets.products || 0,
        gradientFrom: 'from-yellow-400',
        gradientTo: 'to-orange-400',
    },
    {
        title: 'Due',
        value: formatNumber(props.widgets.due || 0),
        gradientFrom: 'from-green-400',
        gradientTo: 'to-teal-400',
    },
    {
        title: 'Sale Assets (Buy Price)',
        value: formatNumber(props.widgets.sale_assets?.buy_price || 0),
        gradientFrom: 'from-purple-500',
        gradientTo: 'to-pink-400',
    },
    {
        title: 'Sale Assets (Sale Price)',
        value: formatNumber(props.widgets.sale_assets?.sale_price || 0),
        gradientFrom: 'from-cyan-400',
        gradientTo: 'to-blue-400',
    },
]);
</script>

<style scoped>
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}
.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c5c5c5;
    border-radius: 4px;
}
.dark .overflow-x-auto::-webkit-scrollbar-track {
    background: #374151;
}
.dark .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #6b7280;
}
</style>
