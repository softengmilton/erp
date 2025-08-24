<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { router } from '@inertiajs/vue3';

const filterToggle = ref(false);
const props = defineProps({
  month: String,
  dailyCategoryData: Object,
  dailyTotalSales: Object,
  dailyTotalCost: Object,
  payments: Array,
});

const today = new Date();
console.log(today);
const selectedRange = ref([today, null])

console.log("Initial range:", selectedRange.value);

function formatDate(date) {
  if (!date) return null; // in case it's null
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

const filterData = ref({
  startDate: today ? formatDate(today) : null,
  endDate: null,
});

// watcher to auto-apply range when changed
watch(selectedRange, (newRange) => {
    filterData.value.startDate = newRange[0] ? formatDate(newRange[0]) : null;
    filterData.value.endDate = newRange[1] ? formatDate(newRange[1]) : null;
    applyRange();
}, { deep: true });

function applyRange() {
  // Send array to Laravel
    router.get('/store/reports', selectedRange.value, {
    preserveState: true,
  });
}

// Extract all unique category names across all days
const categories = Array.from(
  new Set(
    Object.values(props.dailyCategoryData).flatMap((categoriesByDate) =>
      Object.keys(categoriesByDate)
    )
  )
);

const toggleFilter = () => {
  filterToggle.value = !filterToggle.value;
};



const breadcrumbs = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Reports", href: "/store/reports" },
];
</script>

<template>
  <Head title="Reports" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-8">
      <!-- KPI Cards -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Sales Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">💰</span>
            <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">BDT 5,000</p>
          <p class="text-xs text-green-600 mt-1">▲ 12% from last week</p>
        </div>

        <!-- Profit Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">📈</span>
            <h3 class="text-sm font-medium text-gray-500">Profit</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">BDT 3,200</p>
          <p class="text-xs text-green-600 mt-1">▲ 8% from last week</p>
        </div>

        <!-- Orders Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">🛒</span>
            <h3 class="text-sm font-medium text-gray-500">Orders</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">120</p>
          <p class="text-xs text-red-600 mt-1">▼ 3% from last week</p>
        </div>

        <!-- Best Seller Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">🌟</span>
            <h3 class="text-sm font-medium text-gray-500">Best Seller</h3>
          </div>
          <p class="text-lg font-semibold text-gray-900 mt-2">Cold Coffee</p>
          <p class="text-xs text-gray-500 mt-1">Category: Coffee</p>
        </div>
      </div>

      <!-- Filter Toggle Button -->
      <div class="flex justify-end">
        <button
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200"
          @click="toggleFilter"
        >
          <span v-if="!filterToggle">🔍 Show Filters</span>
          <span v-else>❌ Hide Filters</span>
        </button>
      </div>
      <!-- Filters -->
      <div
        v-if="filterToggle"
        class="block bg-gray-50 rounded-lg shadow p-4 mt-6 transition-all duration-300"
      >
        <div class="flex flex-wrap justify-end items-center gap-3 bg-gray-50 rounded-lg p-4">
          <VueDatePicker
            v-model="selectedRange"
            range 
            multi-calendars
            placeholder="Select date range"
            class="min-w-[260px]"
          />

          <span class="text-sm text-gray-600">
            <!-- Optional: show current range -->
          </span>
        </div>
      </div>



      <!-- Export Buttons and Search -->
      <div class="flex items-center justify-between gap-4">
        <!-- Buttons -->
        <div class="flex gap-3">
          <button
            class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded-md shadow-sm"
          >
            📄 Export CSV
          </button>
          <button
            class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded-md shadow-sm"
          >
            🖨️ Print
          </button>
        </div>

        <!-- Search Input -->
        <div class="flex flex-col sm:flex-row sm:items-center">
          <input
            type="text"
            placeholder="Search by product..."
            class="sm:w-64 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          />
        </div>
      </div>

      <!-- Summary Report Table -->
      <div class="bg-white border border-gray-300 rounded-lg p-6 shadow-sm">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 flex items-center gap-3">
          <span class="text-gray-800">Summary Report</span>
          <span class="ml-2 px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded-full">
            {{ props.month }}
          </span>
        </h2>

        <div class="overflow-x-auto border border-gray-300 shadow-sm">
          <table class="min-w-full border-collapse text-sm font-mono">
            <!-- Table Header -->
            <thead class="bg-gray-100">
              <tr>
                <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Date</th>

                <!-- Sales Categories -->
                <th
                  v-for="category in categories"
                  :key="'sales-' + category"
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  {{ category }} Sale
                </th>

                <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Total Sales</th>

                <!-- Cost Categories -->
                <th
                  v-for="category in categories"
                  :key="'cost-' + category"
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  {{ category }} Cost
                </th>

                <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Total Cost</th>

                <!-- Payment Columns -->
                <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Cash</th>
                <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Bkash</th>
                <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Nagad</th>
                <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Due</th>
              </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
              <tr v-for="(categoriesData, date) in dailyCategoryData" :key="date" class="hover:bg-gray-50">
                <!-- Date -->
                <td class="border border-gray-300 px-7 py-2 text-left font-medium bg-white whitespace-nowrap min-w-[120px]">
                  {{ date }}
                </td>

                <!-- Sales per category -->
                <td
                  v-for="category in categories"
                  :key="'sales-data-' + category"
                  class="border border-gray-300 px-4 py-2 text-right text-green-700 bg-white"
                >
                  {{ categoriesData[category]?.category_sales ?? 0 }}
                </td>

                <!-- Total Sales -->
                <td class="border border-gray-300 px-4 py-2 text-right font-semibold bg-gray-50">
                  {{ dailyTotalSales[date] ?? 0 }}
                </td>

                <!-- Cost per category -->
                <td
                  v-for="category in categories"
                  :key="'cost-data-' + category"
                  class="border border-gray-300 px-4 py-2 text-right text-red-600 bg-white"
                >
                  {{ categoriesData[category]?.category_unit_cost ?? 0 }}
                </td>

                <!-- Total Cost -->
                <td class="border border-gray-300 px-4 py-2 text-right font-semibold bg-gray-50">
                  {{ dailyTotalCost[date] ?? 0 }}
                </td>

                <!-- Payment Columns -->
                <td class="border border-gray-300 px-4 py-2 text-right bg-white">

                  {{ payments.find((p) => p.order_date === date)?.total_cash_amount ?? 0 }}
                </td>
                <td class="border border-gray-300 px-4 py-2 text-right bg-white">
                  {{ payments.find((p) => p.order_date === date)?.total_bkash_amount ?? 0 }}
                </td>
                <td class="border border-gray-300 px-4 py-2 text-right bg-white">
                  {{ payments.find((p) => p.order_date === date)?.total_nagad_amount ?? 0 }}
                </td>
                <td class="border border-gray-300 px-4 py-2 text-right bg-white">
                  {{ payments.find((p) => p.order_date === date)?.total_due_amount ?? 0 }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
