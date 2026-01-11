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
const selectedMonth = ref(
  props.filters.month
    ? (() => {
        const [year, month] = props.filters.month.split("-").map(Number);
        return { year, month: month - 1 }; // 0-based month for VueDatePicker
      })()
    : { year: new Date().getFullYear(), month: new Date().getMonth() }
);

const tableRef = ref(null);

/* -----------------------------
   CSV Export
----------------------------- */
function exportTableToCSV() {
  const table = tableRef.value;
  if (!table) return;

  const rows = Array.from(table.querySelectorAll("tr"));
  const matrix = [];

  rows.forEach((row, r) => {
    matrix[r] = matrix[r] || [];
    const cells = Array.from(row.querySelectorAll("th, td"));
    let col = 0;
    for (const cell of cells) {
      while (matrix[r][col] !== undefined) col++;
      const text = (cell.innerText || "").trim().replace(/\r?\n|\r/g, " ");
      const rowspan = parseInt(cell.getAttribute("rowspan") || "1", 10);
      const colspan = parseInt(cell.getAttribute("colspan") || "1", 10);
      for (let i = 0; i < rowspan; i++) {
        for (let j = 0; j < colspan; j++) {
          matrix[r + i] = matrix[r + i] || [];
          matrix[r + i][col + j] = text;
        }
      }
      col += colspan;
    }
  });

  const maxCols = Math.max(0, ...matrix.map((row) => (row ? row.length : 0)));
  const csvRows = matrix.map((row) => {
    const cells = [];
    for (let c = 0; c < maxCols; c++) {
      const cell = row && row[c] !== undefined ? row[c] : "";
      cells.push(`"${String(cell).replace(/"/g, '""')}"`);
    }
    return cells.join(",");
  });

  const csvString = "\uFEFF" + csvRows.join("\n");
  const blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.setAttribute("download", "stock_report.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

/* -----------------------------
   Print Table
----------------------------- */
function printTable() {
  if (!tableRef.value) return;

  const tableHTML = tableRef.value.outerHTML;
  const style = `
    <style>
      body { font-family: sans-serif; padding: 20px; }
      table { border-collapse: collapse; width: 100%; font-size: 12px; }
      th, td { border: 1px solid #000; padding: 6px; text-align: center; }
      th { background: #f4f4f4; font-weight: bold; }
      td { background: #fff; }
      th, td { page-break-inside: avoid; }
      thead { display: table-header-group; } /* repeat header on multiple pages */
      tr { page-break-inside: avoid; }
      body { -webkit-print-color-adjust: exact; }
    </style>
  `;

  const printWindow = window.open("", "_blank", "width=900,height=650");
  printWindow.document.write(`
    <html>
      <head>
        <title>Stock Report</title>
        ${style}
      </head>
      <body>
        <h2 style="text-align:center; margin-bottom:20px;">Stock Report</h2>
        ${tableHTML}
      </body>
    </html>
  `);

  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
}


/* -----------------------------
   Watch Filters
----------------------------- */
watch(
  [selectedCategory, selectedMonth],
  ([category, month]) => {
    const monthStr = month
      ? `${month.year}-${String(month.month + 1).padStart(2, "0")}`
      : null;

    router.get(
      "/store/stock-reports",
      {
        month: monthStr,
        category_id: category || null,
      },
      { preserveState: true, replace: true }
    );
  },
  { immediate: true }
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
        <div class="flex items-center justify-between gap-4">
          <div class="flex gap-2">
            <button @click="exportTableToCSV" class="flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 shadow-sm hover:bg-gray-50">
              📄 Export CSV
            </button>
            <button @click="printTable" class="flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 shadow-sm hover:bg-gray-50">
              🖨 Print
            </button>
          </div>
        </div>

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
          <select v-model="selectedCategory" class="w-full rounded border border-gray-300 p-2">
            <option value="">All Categories</option>
            <option v-for="cat in props.allCategory" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>
      </div>

      <!-- Report Table -->
      <div v-if="Object.keys(props.report).length" class="overflow-x-auto">
        <table ref="tableRef" class="min-w-full border-collapse border border-black text-sm">
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
                  <td class="border px-4 py-2">৳{{ formatNumber(row.total_sale || 0) }}</td>
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
