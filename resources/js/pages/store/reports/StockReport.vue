<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { formatNumber } from "@/utils/helper";

/* -----------------------------
   Props
----------------------------- */
const props = defineProps({
  report: Object,
  allCategory: Array,
  filters: Object,
});

/* -----------------------------
   Filters State
----------------------------- */
// Category
const selectedCategory = ref(props.filters.category_id || "");

// Month-Year picker (single month)
// Month-Year picker (single month)
const selectedMonth = ref(
  props.filters.month
    ? (() => {
        const [year, month] = props.filters.month.split("-").map(Number);
        return { year, month: month - 1 }; // VueDatePicker expects 0-based month
      })()
    : (() => {
        const now = new Date();
        return { year: now.getFullYear(), month: now.getMonth() }; // current month
      })()
);


/* -----------------------------
   Watch Filters
----------------------------- */
watch(
  [selectedCategory, selectedMonth],
  ([category, month]) => {
    // If month is null (cleared), skip
    if (!month) return;

    // Ensure month is a Date instance
    // const date = month instanceof Date ? month : new Date(month);
    // const monthValue = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}`;

    console.log("Selected Month:", selectedMonth.value.month);
    console.log("Selected Category:", category);

    router.get(
      "/store/stock-reports",
      {
        month: month ? selectedMonth.value.year + "-" + String(selectedMonth.value.month + 1).padStart(2, "0") : null,
        category_id: category || null,
      },
      { preserveState: true, replace: true }
    );
  },
  { immediate: true } // ✅ Trigger immediately on mount
);


/* -----------------------------
   Breadcrumbs
----------------------------- */
const breadcrumbs = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Stock Reports", href: "/store/stock-reports" },
];
</script>

<template>
  <Head title="Stock Reports" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-8 p-6">

      <!-- Filters -->
      <div class="flex flex-wrap gap-4 items-center rounded-lg bg-gray-50 p-4 shadow">

        <!-- Month-Year Picker -->
        <div class="min-w-[220px]">
          <VueDatePicker
            v-model="selectedMonth"
            month-picker
            format="yyyy-MM"
            placeholder="Select Month"
            :auto-apply="true"
            :clearable="true"
            :max-date="new Date()"
            class="w-full"
          />
        </div>

        <!-- Category -->
        <div class="min-w-[200px]">
          <select
            v-model="selectedCategory"
            class="w-full rounded border border-gray-300 p-2"
          >
            <option value="">All Categories</option>
            <option
              v-for="cat in props.allCategory"
              :key="cat.id"
              :value="cat.id"
            >
              {{ cat.name }}
            </option>
          </select>
        </div>

      </div>

      <!-- Report Table -->
      <div v-if="Object.keys(props.report).length" class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-black text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="border px-4 py-2 text-left">Invoice</th>
              <th class="border px-4 py-2 text-left">Product</th>
              <th class="border px-4 py-2 text-left">Initial Stock</th>
              <th class="border px-4 py-2 text-left">Sold Qty</th>
              <th class="border px-4 py-2 text-left">Adjustment</th>
              <th class="border px-4 py-2 text-left">Available Stock</th>
              <th class="border px-4 py-2 text-left">Total Sale</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(products, invoice) in props.report" :key="invoice">
              <template v-for="(row, productName) in products" :key="productName">
                <tr>
                  <td class="border px-4 py-2">{{ invoice }}</td>
                  <td class="border px-4 py-2">{{ productName }}</td>
                  <td class="border px-4 py-2">{{ row.initial_stock }}</td>
                  <td class="border px-4 py-2">{{ row.sold_qty }}</td>
                  <td class="border px-4 py-2">{{ row.adjustment }}</td>
                  <td class="border px-4 py-2">{{ row.available_stock }}</td>
                  <td class="border px-4 py-2">
                    ৳{{ formatNumber(row.total_sale) }}
                  </td>
                </tr>
              </template>
            </template>
          </tbody>
        </table>
      </div>

      <div v-else class="p-6 text-center text-gray-500">
        No data available for the selected filters.
      </div>

    </div>
  </AppLayout>
</template>
