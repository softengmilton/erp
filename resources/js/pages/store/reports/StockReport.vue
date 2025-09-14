<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const months = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
];
const filterToggle = ref(true);

const props = defineProps({
  report: Object,
  allCategory: Array,
});

const selectedCategory = ref();
const selectedMonth = ref();

const filterData = ref({
  category_id: 0,
  month: 0,
});

const adjustMonth = () => {
    router.post('/store/adjusted-month');
}

// Watch filters → request new report
watch([selectedCategory, selectedMonth], ([newCat, newMonth]) => {
  filterData.value.category_id = newCat;
  filterData.value.month = newMonth;
  applyRange();
});

function applyRange() {
  router.get("/store/stock-reports", filterData.value, {
    preserveState: true,
    replace: true,
  });
}

const breadcrumbs = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Stock Reports", href: "/store/stock-reports" },
];
</script>

<template>
  <Head title="Reports" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-8">
      <!-- Export Buttons + Filter Toggle -->
      <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
          <div class="flex gap-2">
            <button
              class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded-md shadow-sm"
            >
              📄 Export CSV
            </button>

            <button
              class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded-md shadow-sm"
            >
              🖨 Print
            </button>
            <button
            @click="adjustMonth"
              class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded-md shadow-sm"
            >
              🖨 Adjust Month
            </button>
          </div>
        </div>

        <!-- Filters -->
        <div
          v-if="filterToggle"
          class="bg-gray-50 rounded-lg shadow p-4 transition-all duration-300"
        >
          <div class="flex flex-wrap justify-between items-center gap-4">
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

            <div class="min-w-[200px]">
              <select
                id="category"
                v-model="selectedCategory"
                class="w-full border border-gray-300 rounded p-2"
              >
                <option value="">All Categories</option>
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

      <!-- Report Section -->
      <div class="p-6">
        <div class="overflow-x-auto">
          <table class="min-w-full border border-black text-sm font-mono border-collapse">
            <thead class="bg-gray-100">
              <tr>
                <th class="border border-black px-4 py-2 text-left">Month</th>
                <th class="border border-black px-4 py-2 text-left">Product Name</th>
                <th class="border border-black px-4 py-2 text-left">Stock Number</th>
                <th class="border border-black px-4 py-2 text-left">Sale</th>
                <th class="border border-black px-4 py-2 text-left">Stock</th>
              </tr>
            </thead>
            <tbody>
              <!-- Loop months -->
              <template v-for="(categories, month) in props.report" :key="month">
                <template v-for="(items, category) in categories" :key="category">
                  <template v-for="(row, rowIdx) in items" :key="rowIdx">
                    <tr>
                      <!-- Month column only for first product+row of the month -->
                      <td
                        v-if="rowIdx === 0 && category === Object.keys(categories)[0]"
                        :rowspan="Object.values(categories).flat().length"
                        class="border border-black px-4 py-2 align-top font-bold"
                      >
                        {{ month }}
                      </td>

                      <!-- Product column only for first row of each product -->
                      <td
                        v-if="rowIdx === 0"
                        :rowspan="items.length"
                        class="border border-black px-4 py-2 align-top font-semibold"
                      >
                        {{ category }}
                      </td>

                      <!-- Stock details -->
                      <td class="border border-black px-4 py-2">
                        {{ row.stock_number }}
                      </td>
                      <td class="border border-black px-4 py-2">{{ row.sale }}</td>
                      <td class="border border-black px-4 py-2">{{ row.stock }}</td>
                    </tr>
                  </template>
                </template>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
