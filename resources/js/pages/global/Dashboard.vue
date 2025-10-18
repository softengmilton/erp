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

      <!-- Second row: stats -->
      <!-- Second row: stats (compact) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 auto-rows-min">
        <Widget
          v-for="(item, index) in secondRowWidgets"
          :key="index"
          :title="item.title"
          :value="item.value"
          :gradientFrom="item.gradientFrom"
          :gradientTo="item.gradientTo"
          class="p-2 sm:p-3 text-sm sm:text-base min-h-[80px]"
        />
      </div>

      <!-- Third row: Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div
          class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
          <SpilineAreaChart :chartData="monthlyData" />
        </div>
        <div
          class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
          <StackedColumnsChart :chartData="salesByType" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
/**
 * DASHBOARD PAGE
 * - Fixes chart props passing
 * - Fixes type errors in chartData
 * - Fully compatible with Inertia backend (DashboardController)
 */
import AppLayout from "@/layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import Widget from "@/components/Widget.vue";
import SpilineAreaChart from "@/components/charts/SpilineAreaChart.vue";
import StackedColumnsChart from "@/components/charts/StackedColumnsChart.vue";
import { ref } from "vue";
import * as LucideIcons from "lucide-vue-next";

// ✅ Receive data from backend (Inertia)
const props = defineProps({
  widgets: { type: Object, default: () => ({}) },
  monthlyData: {
    type: Object,
    default: () => ({ months: [], revenue: [], expenses: [] }),
  },
  salesByType: { type: Object, default: () => ({ months: [], data: [] }) },
  productTypes: { type: Array, default: () => [] },
});

console.log("Dashboard props:", props.salesByType);

// Breadcrumbs
const breadcrumbs = [{ title: "Dashboard", href: "/dashboard" }];

// Selected index (for top row highlight)
const selectedIndex = ref(0);

// First row widgets (category icons)
const firstRowWidgets = [
  {
    title: "Store",
    gradientFrom: "from-pink-500",
    gradientTo: "to-red-400",
    icon: LucideIcons.Store,
  },
  {
    title: "Restaurant",
    gradientFrom: "from-green-400",
    gradientTo: "to-teal-400",
    icon: LucideIcons.Coffee,
  },
  {
    title: "War House",
    gradientFrom: "from-pink-400",
    gradientTo: "to-red-400",
    icon: LucideIcons.Home,
  },
  {
    title: "Convention Hall",
    gradientFrom: "from-yellow-400",
    gradientTo: "to-orange-400",
    icon: LucideIcons.Home,
  },
  {
    title: "Park",
    gradientFrom: "from-blue-500",
    gradientTo: "to-indigo-400",
    icon: LucideIcons.ShoppingCart,
  },
  {
    title: "Resort",
    gradientFrom: "from-purple-500",
    gradientTo: "to-pink-400",
    icon: LucideIcons.Home,
  },
  {
    title: "Swimming Pool",
    gradientFrom: "from-cyan-400",
    gradientTo: "to-blue-400",
    icon: LucideIcons.Coffee,
  },
  {
    title: "Ride",
    gradientFrom: "from-pink-500",
    gradientTo: "to-red-400",
    icon: LucideIcons.ShoppingCart,
  },
  {
    title: "Children Zone",
    gradientFrom: "from-blue-500",
    gradientTo: "to-indigo-400",
    icon: LucideIcons.Home,
  },
  {
    title: "Boat Ride",
    gradientFrom: "from-yellow-400",
    gradientTo: "to-orange-400",
    icon: LucideIcons.ShoppingCart,
  },
  {
    title: "LPG Station",
    gradientFrom: "from-pink-500",
    gradientTo: "to-red-400",
    icon: LucideIcons.Home,
  },
];

// Second row stats (dynamic from backend)
const secondRowWidgets = [
  {
    title: "Sales",
    value: `${Number(props.widgets.sales?.month || 0).toLocaleString()}`,
    gradientFrom: "from-blue-500",
    gradientTo: "to-indigo-400",
  },
  {
    title: "Revenue",
    value: `${Number(props.widgets.revenue?.month || 0).toLocaleString()}`,
    gradientFrom: "from-pink-500",
    gradientTo: "to-red-400",
  },
  {
    title: "Products",
    value: `${props.widgets.products || 0}`,
    gradientFrom: "from-yellow-400",
    gradientTo: "to-orange-400",
  },
  {
    title: "Due",
    value: `${Number(props.widgets.due || 0).toLocaleString()}`,
    gradientFrom: "from-green-400",
    gradientTo: "to-teal-400",
  },
  {
    title: "Sale Assets (Buy Price)",
    value: `${Number(props.widgets.sale_assets.buy_price || 0).toLocaleString()}`,
    gradientFrom: "from-purple-500",
    gradientTo: "to-pink-400",
  },
  {
    title: "Sale Assets (Sale Price)",
    value: `${Number(props.widgets.sale_assets.sale_price || 0).toLocaleString()}`,
    gradientFrom: "from-cyan-400",
    gradientTo: "to-blue-400",
  },
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
