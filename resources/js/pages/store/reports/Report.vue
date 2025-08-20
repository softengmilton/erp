<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
// import { Head, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
// import { ref, watch } from 'vue';
// import debounce from 'lodash/debounce';

const props = usePage().props;

const month = props.month;
const dailyCategoryData = props.dailyCategoryData;
const dailyTotalSales = props.dailyTotalSales;
const dailyTotalCost = props.dailyTotalCost;
const payments = props.payments;

// Get all unique categories from the month data
const categories = Array.from(new Set(
    Object.values(dailyCategoryData)
          .flatMap(day => Object.keys(day))
));


const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Reports', href: '/store/reports' },
];
</script>

<template>
  <Head title="Reports" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-8">
      <!-- KPI Cards -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white rounded-xl shadow p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">💰</span>
            <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">BDT 5,000</p>
          <p class="text-xs text-green-600 mt-1">▲ 12% from last week</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">📈</span>
            <h3 class="text-sm font-medium text-gray-500">Profit</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">BDT 3,200</p>
          <p class="text-xs text-green-600 mt-1">▲ 8% from last week</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">🛒</span>
            <h3 class="text-sm font-medium text-gray-500">Orders</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">120</p>
          <p class="text-xs text-red-600 mt-1">▼ 3% from last week</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">🌟</span>
            <h3 class="text-sm font-medium text-gray-500">Best Seller</h3>
          </div>
          <p class="text-lg font-semibold text-gray-900 mt-2">Cold Coffee</p>
          <p class="text-xs text-gray-500 mt-1">Category: Coffee</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white shadow rounded-xl p-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
          <input
            type="text"
            placeholder="Search by product..."
            class="sm:w-64 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          />
        </div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
          <select v-model="selectedCategory" class="block rounded-lg border p-2.5 text-sm">
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
      <div class="flex gap-3">
        <button class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md">
          📄 Export CSV
        </button>
        <button class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md">
          🖨️ Print
        </button>
      </div>

      <!-- Summary Report category-column -->
      <div class="bg-white shadow-lg rounded-2xl p-6">
      <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-6 flex items-center gap-3">
        <span class="bg-gradient-to-r from-purple-500 via-pink-500 to-red-400 bg-clip-text text-transparent">
          Summary Report
        </span>
        <span class="ml-2 px-3 py-1 text-sm sm:text-base font-semibold text-white bg-blue-600 rounded-full shadow-md">
          {{ props.month }}
        </span>
      </h2>

<div class="overflow-x-auto bg-white rounded-3xl shadow-2xl p-4">
  <table class="min-w-full border-collapse">
    <!-- Table Header -->
    <thead class="bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 shadow-inner">
      <tr>
        <th class="sticky top-0 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th>

        <!-- Sales Categories -->
        <th v-for="category in categories" 
            :key="'sales-' + category"
            class="sticky top-0 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
          {{ category }} Sale
        </th>

        <th class="sticky top-0 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-l border-gray-300">
          Total Sales
        </th>

        <!-- Cost Categories -->
        <th v-for="category in categories" 
            :key="'cost-' + category"
            class="sticky top-0 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
          {{ category }} Cost
        </th>

        <th class="sticky top-0 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider border-l border-gray-300">
          Total Cost
        </th>

        <!-- Payment Columns -->
        <th class="sticky top-0 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Cash</th>
        <th class="sticky top-0 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Bkash</th>
        <th class="sticky top-0 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nagad</th>
        <th class="sticky top-0 px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Due</th>
      </tr>
    </thead>

    <!-- Table Body -->
    <tbody class="divide-y divide-gray-200">
      <tr v-for="(categoriesData, date) in dailyCategoryData" :key="date" 
          class="hover:shadow-lg hover:bg-gradient-to-r hover:from-indigo-50 hover:via-purple-50 hover:to-pink-50 transition-all duration-200 rounded-xl">
        <!-- Date Badge -->
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full font-semibold text-xs">{{ date }}</span>
        </td>

        <!-- Sales per category -->
        <td v-for="category in categories" :key="'sales-data-' + category" class="px-6 py-4 whitespace-nowrap text-green-600 font-semibold">
          {{ categoriesData[category]?.category_sales ?? 0 }}
        </td>

        <!-- Total Sales -->
        <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap border-l border-gray-300">
          {{ dailyTotalSales[date] ?? 0 }}
        </td>

        <!-- Cost per category -->
        <td v-for="category in categories" :key="'cost-data-' + category" class="px-6 py-4 whitespace-nowrap text-red-500 font-semibold">
          {{ categoriesData[category]?.category_unit_cost ?? 0 }}
        </td>

        <!-- Total Cost -->
        <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap border-l border-gray-300">
          {{ dailyTotalCost[date] ?? 0 }}
        </td>

        <!-- Payment Columns -->
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
          {{ payments.find(p => p.order_date === date)?.total_cash_amount ?? 0 }}
        </td>
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
          {{ payments.find(p => p.order_date === date)?.total_bkash_amount ?? 0 }}
        </td>
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
          {{ payments.find(p => p.order_date === date)?.total_nagad_amount ?? 0 }}
        </td>
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
          {{ payments.find(p => p.order_date === date)?.total_due_amount ?? 0 }}
        </td>
      </tr>
    </tbody>
  </table>
</div>



      </div>
    </div>
  </AppLayout>
</template>