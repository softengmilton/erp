<template>
  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 flex flex-col gap-6">
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
            'ring-4 ring-offset-2 ring-indigo-500 transform scale-105':
              selectedIndex === index,
          }"
          @click="selectedIndex = index"
        >
          <template #icon>
            <component :is="item.icon" class="w-8 h-8" />
          </template>
        </Widget>
      </div>

      <!-- Second row: 4 stat widgets -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <Widget
          v-for="(item, index) in secondRowWidgets"
          :key="index"
          :title="item.title"
          :value="item.value"
          :gradientFrom="item.gradientFrom"
          :gradientTo="item.gradientTo"
        />
      </div>

      <!-- Third row: Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div
          class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
          <SpilineAreaChart />
        </div>
        <div
          class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
          <StackedColumnsChart />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

import Widget from '@/components/Widget.vue';
import SpilineAreaChart from '@/components/charts/SpilineAreaChart.vue';
import StackedColumnsChart from '@/components/charts/StackedColumnsChart.vue';

// Import icons from lucide-vue-next
import * as LucideIcons from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
];

// Selected index for highlighting
import { ref } from 'vue';
const selectedIndex = ref(0);

// First row widgets (scrollable, icon + name)
const firstRowWidgets = [
  { title: 'Store', gradientFrom: 'from-pink-500', gradientTo: 'to-red-400', icon: LucideIcons.Store },
  { title: 'Restaurant', gradientFrom: 'from-green-400', gradientTo: 'to-teal-400', icon: LucideIcons.Coffee },
  { title: 'War House', gradientFrom: 'from-pink-400', gradientTo: 'to-red-400', icon: LucideIcons.Home },
  { title: 'Convention Hall', gradientFrom: 'from-yellow-400', gradientTo: 'to-orange-400', icon: LucideIcons.Home },
  { title: 'Park', gradientFrom: 'from-blue-500', gradientTo: 'to-indigo-400', icon: LucideIcons.ShoppingCart },
  { title: 'Resort', gradientFrom: 'from-purple-500', gradientTo: 'to-pink-400', icon: LucideIcons.Home },
  { title: 'Swimming Pool', gradientFrom: 'from-cyan-400', gradientTo: 'to-blue-400', icon: LucideIcons.Coffee },
  { title: 'Ride', gradientFrom: 'from-pink-500', gradientTo: 'to-red-400',  icon: LucideIcons.ShoppingCart },
  { title: 'Children Zone', gradientFrom: 'from-blue-500', gradientTo: 'to-indigo-400', icon: LucideIcons.Home },
  { title: 'Boat Ride',  gradientFrom: 'from-yellow-400', gradientTo: 'to-orange-400', icon: LucideIcons.ShoppingCart },
  { title: 'LPG Station',gradientFrom: 'from-pink-500', gradientTo: 'to-red-400', icon: LucideIcons.Home },
];

// Second row widgets (stats)
const secondRowWidgets = [
  { title: 'Sales', value: '$25,000', gradientFrom: 'from-blue-500', gradientTo: 'to-indigo-400' },
  { title: 'Revenue', value: '$40,000', gradientFrom: 'from-pink-500', gradientTo: 'to-red-400' },
  { title: 'Product', value: '120', gradientFrom: 'from-yellow-400', gradientTo: 'to-orange-400' },
  { title: 'Due', value: '$5,000', gradientFrom: 'from-green-400', gradientTo: 'to-teal-400' },
];
</script>

<style scoped>
/* Custom scrollbar styling */
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

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.dark .overflow-x-auto::-webkit-scrollbar-track {
  background: #374151;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
