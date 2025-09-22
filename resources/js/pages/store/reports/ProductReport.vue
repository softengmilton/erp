<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, watch, reactive, computed } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import StoreSetting, { initStoreSetting } from "@/utils/module/StoreSetting";

/**
 * Action Store Settings
 */
initStoreSetting();

const filterToggle = ref(false);
const tableRef = ref(null);

const props = defineProps({
  dateLabel: String,
  allCategory: Array,
  dailyReport: Object,
  total_product_sales: Number,
  total_profit: Number,
  bestSellingCategory: Object,
  bestProfitableCategory: Object,
});

console.log(props.dailyReport)

// Make reactive copies
const reactiveReport = reactive({
  dailyReport: props.dailyReport,
  allCategory: props.allCategory,
  total_product_sales: props.total_product_sales,
  total_profit: props.total_profit,
  bestSellingCategory: props.bestSellingCategory,
  bestProfitableCategory: props.bestProfitableCategory,
});

// console.log(props.dailyReport);

const selectedCategory = ref(null);

const today = new Date();
const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

const selectedRange = ref([firstDay, lastDay]);

function formatDate(date) {
  if (!date) return null;
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

// default: whole current month
const filterData = ref({
  startDate: formatDate(firstDay),
  endDate: formatDate(lastDay),
  category_id: null,
});

// watch date range changes
watch(selectedRange, (newRange) => {
  const start = newRange[0] ? formatDate(newRange[0]) : null;
  const end = newRange[1] ? formatDate(newRange[1]) : start;

  filterData.value.startDate = start;
  filterData.value.endDate = end;

  applyRange();
});

// watch category changes
watch(selectedCategory, (newCat) => {
  filterData.value.category_id = newCat || null;
  applyRange();
});

function applyRange() {
  const payload = {};

  // only send params that are not null
  if (filterData.value.startDate) payload.startDate = filterData.value.startDate;
  if (filterData.value.endDate) payload.endDate = filterData.value.endDate;
  if (filterData.value.category_id) payload.category_id = filterData.value.category_id;

  router.get("/store/products-reports", payload, {
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
      reactiveReport.dailyReport = page.props.dailyReport;
      reactiveReport.total_product_sales = page.props.total_product_sales;
      reactiveReport.total_profit = page.props.total_profit;
      reactiveReport.bestSellingCategory = page.props.bestSellingCategory;
      reactiveReport.bestProfitableCategory = page.props.bestProfitableCategory;
    },
  });
}


const aggregatedCategories = computed(() => {
  return Array.from(
    new Set(
      Object.values(reactiveReport.dailyReport).flatMap((day) =>
        Object.keys(day.categories)
      )
    )
  );
});

/**
 * Export table to CSV
 */

const exportTableToCSV = () => {
  const settings = StoreSetting.all.value;
  const table = document.querySelector("table");
  const rows = table.querySelectorAll("tr");
  const csv = [];
  // Add business info at the top
  csv.push([settings.business_title || "Store Name"]);
  csv.push([settings.address || ""]);
  csv.push([`Report Date: ${new Date().toLocaleDateString()}`]);
  csv.push([]); // empty row

  rows.forEach((row) => {
    const cols = row.querySelectorAll("th, td");
    const rowData = [];
    cols.forEach((col) => {
      const data = col.innerText.trim().replace(/"/g, '""');
      rowData.push(`"${data}"`);
    });
    csv.push(rowData.join(","));
  });

  const csvFile = new Blob([csv.join("\n")], {
    type: "text/csv;charset=utf-8;",
  });

  const link = document.createElement("a");
  link.href = URL.createObjectURL(csvFile);
  link.download = `summary_report_${new Date().toISOString().split("T")[0]}.csv`;
  link.click();
};

/**
 * Print the table
 */
const printTable = () => {
  const settings = StoreSetting.all.value;
  const clone = tableRef.value.cloneNode(true);
  const reportTitle = clone.querySelector("h2");
  if (reportTitle) reportTitle.remove();

  const printWindow = window.open("", "", "width=900,height=650");
  printWindow.document.write(`
    <html>
      <head>
        <title>${settings.business_title || "Store Report"}</title>
        <style>
          body { font-family: sans-serif; padding: 20px; }
          h1, h2 { margin: 0; padding: 0; }
          table { border-collapse: collapse; width: 100%; font-size: 12px; margin-top: 20px; }
          th, td { border: 1px solid #ccc; padding: 6px; text-align: center; }
          th { background: #f4f4f4; }
          .header { text-align: center; margin-bottom: 20px; }
          .logo { max-height: 60px; margin-bottom: 10px; }
        </style>
      </head>
      <body>
        <div class="header">
          ${settings.logo ? `<img src="${settings.logo}" class="logo" />` : ""}
          <h1>${settings.business_title || "Store Name"}</h1>
          <p>${settings.address || ""}</p>
          <p>Report Date: ${new Date().toLocaleDateString()}</p>
            <h5>Total Profit:  BDT ${reactiveReport.total_profit}</h5>
        </div>
        ${clone.innerHTML}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.print();
};

const toggleFilter = () => {
  filterToggle.value = !filterToggle.value;
};

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
            BDT {{ reactiveReport.total_product_sales }}
          </p>
        </div>

        <!-- Profit Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">📈</span>
            <h3 class="text-sm font-medium text-gray-500">Profit</h3>
          </div>
          <p class="text-2xl font-bold text-gray-900 mt-2">
            BDT {{ reactiveReport.total_profit }}
          </p>
        </div>

        <!-- Best Profitable Category Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">💰</span>
            <h3 class="text-sm font-medium text-gray-500">Top Profit Category</h3>
          </div>
          <p class="text-lg font-semibold text-gray-900 mt-2">
            {{ reactiveReport.bestProfitableCategory?.name || "No data" }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Profit: BDT {{ reactiveReport.bestProfitableCategory?.profit || 0 }}
          </p>
        </div>

        <!-- Best Seller Card -->
        <div class="rounded-xl border p-4 flex flex-col items-start">
          <div class="flex items-center gap-2">
            <span class="text-xl">🌟</span>
            <h3 class="text-sm font-medium text-gray-500">Best Seller</h3>
          </div>
          <p class="text-lg font-semibold text-gray-900 mt-2">
            {{ reactiveReport.bestSellingCategory?.name || "No data" }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            Sales: {{ reactiveReport.bestSellingCategory?.sales || 0 }}
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
              @click="exportTableToCSV"
              class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 rounded-md shadow-sm"
            >
              📄 Export CSV
            </button>

            <!-- Print Button -->
            <button
              @click="printTable"
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
              <select
                id="category"
                v-model="selectedCategory"
                class="w-full border border-gray-300 rounded p-2"
              >
                <option
                  v-for="category in reactiveReport.allCategory"
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
              {{ props.dateLabel }}
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
                    :key="category + '-sale-header'"
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
                    :key="category + '-cost-header'"
                    class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                  >
                    {{ category }} Cost
                  </th>

                  <th
                    class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                  >
                    Total Cost
                  </th>
                </tr>
              </thead>

              <!-- Table Body -->
              <tbody>
                <tr
                  v-if="
                    reactiveReport.dailyReport &&
                    Object.keys(reactiveReport.dailyReport).length
                  "
                  v-for="(day, date) in reactiveReport.dailyReport"
                  :key="date"
                  class="hover:bg-gray-50"
                >
                  <td
                    class="border border-gray-300 px-7 py-2 text-left font-medium bg-white whitespace-nowrap min-w-[120px]"
                  >
                    {{ date }}
                  </td>

                  <!-- Sales per category -->
                  <td
                    v-for="category in aggregatedCategories"
                    :key="date + '-' + category + '-sale'"
                    class="border border-gray-300 px-2 py-2 text-left text-green-700 bg-white"
                  >
                   Qty {{ day.categories[category]?.product_quantity ?? 0}} | Sales {{ day.categories[category]?.product_sales ?? 0 }}
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
                    :key="date + '-' + category + '-cost'"
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
                </tr>

                <!-- No Data Row -->
                <tr v-else>
                  <td colspan="100%" class="text-center py-4 text-gray-500">
                    No data found
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
