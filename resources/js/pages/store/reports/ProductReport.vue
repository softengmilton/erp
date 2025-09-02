<script setup>
  import AppLayout from "@/layouts/AppLayout.vue";
  import { Head, router } from "@inertiajs/vue3";
  import { ref, watch } from "vue";
  import VueDatePicker from "@vuepic/vue-datepicker";
  import "@vuepic/vue-datepicker/dist/main.css";

  const filterToggle = ref(false);

const props = defineProps({
    // allCategory: Array,
    dailyReport: Object,
    total_product_sales: Number,
    total_profit: Number,
    bestSellingCategory: Object,        
    bestProfitableCategory: Object,
  });


// const selectedCategoryy = ref(0);
// console.log(selectedCategoryy.value);

  const today = new Date();
  console.log(today);

  const selectedRange = ref([today, null]);
  console.log(selectedRange);

  function formatDate(date) {
    if (!date) return null;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  const filterData = ref({
    startDate: today ? formatDate(today) : null,
    endDate: null,
  });

  // Watcher: apply range on change
  watch(
    selectedRange,
    (newRange) => {
      filterData.value.startDate = newRange[0] ? formatDate(newRange[0]) : null;
      filterData.value.endDate = newRange[1] ? formatDate(newRange[1]) : null;
      applyRange();
    },
    { deep: true }
  );

    function applyRange() {
    router.get("/store/products-reports", filterData.value, {
      preserveState: true,
    });
  }

const aggregatedCategories = Array.from(
  new Set(
    Object.values(props.dailyReport).flatMap( (day) => Object.keys(day.categories) )
  )
);



const  toggleFilter = () => {
  filterToggle.value = !filterToggle.value;
}

  const breadcrumbs = [
    { title: "Dashboard", href: "/dashboard" },
    { title: "Prodcuts Reports", href: "/store/products-reports" },
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
            BDT {{ total_product_sales }}
          </p>
        </div>

        <!-- Profit Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">📈</span>
            <h3 class="text-sm font-medium text-gray-500">Profit</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">
            BDT {{ total_profit }}
          </p>
        </div>

        <!-- Best Profitable Category Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">💰</span>
            <h3 class="text-sm font-medium text-gray-500">Top Profit Category</h3>
          </div>
          <p class="text-lg font-semibold text-gray-900 mt-2">
            {{ props.bestProfitableCategory?.name || 'No data' }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Profit: BDT {{ props.bestProfitableCategory?.profit || 0 }}
          </p>
        </div>


        <!-- Best Seller Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">🌟</span>
            <h3 class="text-sm font-medium text-gray-500">Best Seller</h3>
          </div>
          <p class="text-lg font-semibold text-gray-900 mt-2">
            {{ props.bestSellingCategory?.name || "No data" }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Sales: {{ props.bestSellingCategory?.sales || 0 }}
          </p>
        </div>
      </div>

      <!-- Export Buttons + Filter Toggle -->
      <div class="flex flex-col gap-4">
        <!-- Buttons Row -->
        <div class="flex items-center justify-between gap-4">
          <!-- Export Button -->
          <button
            @click="exportTableToCSV"
            class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded-md shadow-sm"
          >
            📄 Export CSV
          </button>

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

        <!-- Filters Row (only shows when true) -->
        <div
          v-if="filterToggle"
          class="bg-gray-50 rounded-lg shadow p-4 transition-all duration-300"
        >
          <div class="flex flex-wrap justify-between items-center gap-4">
            <!-- Date Picker -->
            <div class="min-w-[260px]">
              <VueDatePicker
                v-model="selectedRange"
                range
                multi-calendars
                placeholder="Select date range"
                class="w-full border rounded p-2"
              />
            </div>

            <!-- Category Selector -->
            <div class="min-w-[200px]">
              <!-- <label for="category" class="block text-gray-700 text-sm mb-1">Category</label> -->
              <select
                id="category"
                v-model="selectedCategory"
                class="w-full border border-gray-300 rounded p-2"
              >
                <option>All Categories</option>
                <!-- <option
                  v-for="category in allCategory"
                  :key="category.id"
                  :value="5"
                >
                    {{ category.name }}
                </option> -->
              </select>
            </div>
          </div>
        </div>
      </div>


      <!-- Summary Report Table -->
      <div class="bg-white border border-gray-300 rounded-lg p-6 shadow-sm">
        <h2
          class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 flex items-center gap-3"
        >
          <span class="text-gray-800">Summary Report</span>
          <span
            class="ml-2 px-3 py-1 text-sm font-semibold text-white bg-blue-600 rounded-full"
          >
            Augst
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
                  v-for="category in aggregatedCategories"
                  :key="category"
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
                  v-for="category in aggregatedCategories"
                  :key="category"
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
                <th
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  Cash
                </th>
                <th
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  Bkash
                </th>
                <th
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  Nagad
                </th>
                <th
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  Due
                </th>
              </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
              <tr 
                v-for="(day, date) in props.dailyReport"
                :key="date"
                class="hover:bg-gray-50"
              >
                <!-- Date -->
                <td
                  class="border border-gray-300 px-7 py-2 text-left font-medium bg-white whitespace-nowrap min-w-[120px]"
                >
                  {{ date }}
                </td>

                <!-- Sales per category -->
                <td
                  v-for="category in aggregatedCategories"
                  :key="category"
                  class="border border-gray-300 px-4 py-2 text-right text-green-700 bg-white"
                >
                  {{ day.categories[category]?.product_sales ?? 0}}
                </td>

                <!-- Total Sales -->
                <td
                  class="border border-gray-300 px-4 py-2 text-right font-semibold bg-gray-50"
                >
                  {{ day.total_sales }}
                </td>

                <!-- Cost per category -->
                <td
                  v-for="category in aggregatedCategories"
                  :key="category"
                  class="border border-gray-300 px-4 py-2 text-right text-red-600 bg-white"
                >
                  {{ day.categories[category]?.product_cost ?? 0 }}
                </td>

                <!-- Total Cost -->
                <td
                  class="border border-gray-300 px-4 py-2 text-right font-semibold bg-gray-50"
                >
                  {{ day.total_cost }}
                </td>

                <!-- Payment Columns -->
                <td class="border border-gray-300 px-4 py-2 text-right bg-white">
                  
                </td>
                <td class="border border-gray-300 px-4 py-2 text-right bg-white">
                  
                </td>
                <td class="border border-gray-300 px-4 py-2 text-right bg-white">
                  
                </td>
                <td class="border border-gray-300 px-4 py-2 text-right bg-white">
                  
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
