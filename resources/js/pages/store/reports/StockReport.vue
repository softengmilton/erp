<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, watch, reactive, computed } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";


const months = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];
const filterToggle = ref(false);
const props = defineProps({
  allCategory: Array,
  stockReport: Object,
});

console.log(props.allCategory);


const selectedCategory = ref();
const selectedMonth = ref();


const filterData = ref({
  category_id: 0,
  month: 0,
});

watch(
  [selectedCategory, selectedMonth],
  ([newCat, new_month]) => {
    filterData.value.category_id = newCat;
    filterData.value.month = new_month;
    console.log(filterData.value.category_id);
    console.log(filterData.value.month);
    applyRange();
  },
  { deep: true }
);

function applyRange() {
  router.get("/store/stock-reports", filterData.value, {
    preserveState: true,
    replace: true,
  });
}

const aggregatedCategories = computed(() => {
  return Array.from(
    new Set(
      Object.values(props.stockReport).flatMap((day) =>
        Object.keys(day.categories)
      )
    )
  );
});

console.log(aggregatedCategories.value);

const toggleFilter = () => {
  filterToggle.value = !filterToggle.value;
};

const breadcrumbs = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Stock Reports", href: "/store/stock-reports" },
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
          <p class="text-2xl font-bold text-gray-900 mt-2">
            BDT 
          </p>
        </div>

        <!-- Profit Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">📈</span>
            <h3 class="text-sm font-medium text-gray-500">Profit</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">
            BDT
          </p>
        </div>

        <!-- Best Profitable Category Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">💰</span>
            <h3 class="text-sm font-medium text-gray-500">Top Profit Category</h3>
          </div>
          <p class="text-lg font-semibold text-gray-900 mt-2">
            aa
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Profit: BDT 
          </p>
        </div>

        <!-- Best Seller Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">🌟</span>
            <h3 class="text-sm font-medium text-gray-500">Best Seller</h3>
          </div>
          <p class="text-lg font-semibold text-gray-900 mt-2">
            aa
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Sales: 
          </p>
        </div>
      </div>

      <!-- Export Buttons + Filter Toggle -->
      <div class="flex flex-col gap-4">
        <!-- Buttons Row -->
        <div class="flex items-center justify-between gap-4">
          <!-- Export + Print Buttons Group -->
          <div class="flex gap-2">
            <!-- Export Button -->
            <button
              class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded-md shadow-sm"
            >
              📄 Export CSV
            </button>

            <!-- Print Button -->
            <button
              class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded-md shadow-sm"
            >
              🖨 Print
            </button>
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
        </div>

        <!-- Filters Row -->
        <div
          v-if="filterToggle"
          class="bg-gray-50 rounded-lg shadow p-4 transition-all duration-300"
        >
          <div class="flex flex-wrap justify-between items-center gap-4">
            <!-- Date Picker -->
            <div class="min-w-[260px]">
               <select
                v-model="selectedMonth"
                class="w-full border border-gray-300 rounded p-2"
                >
                <option value="">Select Month</option>
                <option
                  v-for="(monthName, index) in months"
                  :key="index"
                  :value="index + 1"
                >
                  {{ monthName }}
                </option>
              </select>
            </div>

            <!-- Category Selector -->
            <div class="min-w-[200px]">
              <select
                id="category"
                v-model="selectedCategory"
                class="w-full border border-gray-300 rounded p-2"
              >
                <option
                  v-for="category in props.allCategory"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Report Table -->
      <div ref="tableRef">
        <div class="bg-white border border-gray-300 rounded-lg p-6 shadow-sm">
          <h2
            class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 flex items-center gap-3"
          >
            <span class="text-gray-800"> Report </span>
            <span
              class="ml-2 px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded-full"
            >
              ww
            </span>
          </h2>

          <div class="overflow-x-auto border border-gray-300 shadow-sm">
            <table class="min-w-full border-collapse text-sm font-mono">
              <!-- Table Header -->
              <thead class="bg-gray-100">
                <tr>
                    <!-- Sales Categories -->
                  <th
                    v-for="category in aggregatedCategories"
                    :key="category + '-sale-header'"
                    class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                  >
                    {{ category }} 
                  </th>

                </tr>
              </thead>

              <!-- Table Body -->
              <tbody>
                <tr class="hover:bg-gray-50">
                  <td
                    v-for="(data, category) in props.stockReport[1].categories"
                    :key="category"
                    class="border border-gray-300 px-4 py-2 text-right text-red-600 bg-white"
                  >
                    {{ data.totalStockLeft }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
