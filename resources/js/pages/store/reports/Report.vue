<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";


const props = defineProps({
  month: String,
  dailyCategoryData: Object,
  dailyTotalSales: Object,
  dailyTotalCost: Object,
  payments: Array,
});

const categories = Array.from(
  new Set(Object.values(props.dailyCategoryData).flatMap((day) => Object.keys(day)))
);

const filterToggle = ref(false);

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
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">💰</span>
            <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">BDT 5,000</p>
          <p class="text-xs text-green-600 mt-1">▲ 12% from last week</p>
        </div>
        <div class="rounded-xl border  p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">📈</span>
            <h3 class="text-sm font-medium text-gray-500">Profit</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">BDT 3,200</p>
          <p class="text-xs text-green-600 mt-1">▲ 8% from last week</p>
        </div>
        <div class="rounded-xl border  p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">🛒</span>
            <h3 class="text-sm font-medium text-gray-500">Orders</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">120</p>
          <p class="text-xs text-red-600 mt-1">▼ 3% from last week</p>
        </div>
        <div class="rounded-xl border  p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">🌟</span>
            <h3 class="text-sm font-medium text-gray-500">Best Seller</h3>
          </div>
          <p class="text-lg font-semibold text-gray-900 mt-2">Cold Coffee</p>
          <p class="text-xs text-gray-500 mt-1">Category: Coffee</p>
        </div>
      </div>
      <!-- // filter expand button -->
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
        class="block sm:flex sm:items-center sm:justify-between bg-gray-50 rounded-lg shadow p-4 mt-6 transition-all duration-300"
      >

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
          <input
            type="text"
            placeholder="Search by product..."
            class="sm:w-64 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          />
        </div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
          <select
            v-model="selectedCategory"
            class="block rounded-lg border p-2.5 text-sm"
          >
            <option disabled value="">Select Category</option>
            <option>Coffee</option>
            <option>Chocolate</option>
            <option>Ice cream</option>
            <option>Drinks</option>
          </select>
          <select v-model="selectedRange" class="block rounded-lg border p-2.5 text-sm">
            <option disabled value="">Select Range</option>
            <option>Today</option>
            <option>This Week</option>
            <option>Last 10 Days</option>
            <option>Last 15 Days</option>
            <option>This Month</option>
            <option>This Year</option>
          </select>
        </div>
      </div>

      <!-- Export Buttons -->
 <!-- Export Buttons -->
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

  <!-- Summary Report category-column -->
  <div class="bg-white border border-gray-300 rounded-lg p-6 shadow-sm">
    <h2
      class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 flex items-center gap-3"
    >
      <span class="text-gray-800">
        Summary Report
      </span>
      <span
        class="ml-2 px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded-full"
      >
        {{ props.month }}
      </span>
    </h2>

    <div class="overflow-x-auto border border-gray-300 shadow-sm">
      <table class="min-w-full border-collapse text-sm font-mono">
        <!-- Table Header -->
        <thead class="bg-gray-100">
          <tr>
            <th
              class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
            >
              Date
            </th>

            <!-- Sales Categories -->
            <th
              v-for="category in categories"
              :key="'sales-' + category"
              class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
            >
              {{ category }} Sale
            </th>

            <th
              class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
            >
              Total Sales
            </th>

            <!-- Cost Categories -->
            <th
              v-for="category in categories"
              :key="'cost-' + category"
              class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
            >
              {{ category }} Cost
            </th>

            <th
              class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
            >
              Total Cost
            </th>

            <!-- Payment Columns -->
            <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Cash</th>
            <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Bkash</th>
            <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Nagad</th>
            <th class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100">Due</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody>
          <tr
            v-for="(categoriesData, date) in dailyCategoryData"
            :key="date"
            class="hover:bg-gray-50"
          >
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
