<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
  report: Object,
  allCategory: Array,
  filters: Object,
});

const months = [
  'January','February','March','April','May','June','July','August','September','October','November','December'
];

const filterToggle = ref(true);
const selectedCategory = ref(props.filters.category_id || '');
const selectedMonth = ref(props.filters.month ? Number(props.filters.month.split('-')[1]) : '');

// Watch filters → fetch new report
watch([selectedCategory, selectedMonth], ([newCat, newMonth]) => {
  // Convert month number to YYYY-MM
  const monthValue = newMonth ? `${new Date().getFullYear()}-${String(newMonth).padStart(2,'0')}` : null;
  const categoryValue = newCat ? newCat : null;

  router.get('/store/stock-reports', {
    month: monthValue,
    category_id: categoryValue,
  }, { preserveState: true, replace: true });
});

// Table ref
const tableRef = ref(null);

// CSV Export
function exportTableToCSV() {
  const table = tableRef.value;
  if (!table) return;

  const rows = Array.from(table.querySelectorAll('tr'));
  const matrix = [];

  rows.forEach((row, r) => {
    matrix[r] = matrix[r] || [];
    const cells = Array.from(row.querySelectorAll('th, td'));
    let col = 0;
    for (const cell of cells) {
      while (matrix[r][col] !== undefined) col++;
      const text = (cell.innerText || '').trim().replace(/\r?\n|\r/g, ' ');
      const rowspan = parseInt(cell.getAttribute('rowspan') || '1', 10);
      const colspan = parseInt(cell.getAttribute('colspan') || '1', 10);
      for (let i = 0; i < rowspan; i++) {
        for (let j = 0; j < colspan; j++) {
          matrix[r + i] = matrix[r + i] || [];
          matrix[r + i][col + j] = text;
        }
      }
      col += colspan;
    }
  });

  const maxCols = Math.max(0, ...matrix.map(row => (row ? row.length : 0)));
  const csvRows = matrix.map(row => {
    const cells = [];
    for (let c = 0; c < maxCols; c++) {
      const cell = row && row[c] !== undefined ? row[c] : '';
      cells.push(`"${String(cell).replace(/"/g, '""')}"`);
    }
    return cells.join(',');
  });

  const csvString = '\uFEFF' + csvRows.join('\n');
  const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', 'stock_report.csv');
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Print Table
function printTable() {
  const printContent = tableRef.value.innerHTML;
  const printWindow = window.open('', '', 'width=900,height=650');
  printWindow.document.write(`
    <html>
      <head>
        <title>Stock Report</title>
        <style>
          body { font-family: sans-serif; padding: 20px; }
          table { border-collapse: collapse; width: 100%; font-size: 12px; }
          th, td { border: 1px solid #000; padding: 6px; text-align: center; }
          th { background: #f4f4f4; }
        </style>
      </head>
      <body>
        ${printContent}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.print();
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Stock Reports', href: '/store/stock-reports' },
];
</script>
<template>
  <Head title="Stock Reports" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-8 p-6">

      <!-- Filters + Export Buttons -->
      <div class="flex flex-col gap-4">
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

        <div v-if="filterToggle" class="rounded-lg bg-gray-50 p-4 shadow">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="min-w-[260px]">
              <select v-model="selectedMonth" class="w-full rounded border border-gray-300 p-2">
                <option value="">All Months</option>
                <option v-for="(monthName, idx) in months" :key="idx" :value="idx + 1">{{ monthName }}</option>
              </select>
            </div>
            <div class="min-w-[200px]">
              <select v-model="selectedCategory" class="w-full rounded border border-gray-300 p-2">
                <option value="">All Categories</option>
                <option v-for="cat in props.allCategory" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Table -->
      <div v-if="Object.keys(props.report).length" class="overflow-x-auto" ref="tableRef">
        <table class="min-w-full border-collapse border border-black font-mono text-sm">
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
                  <td class="border px-4 py-2">${{ row.total_sale }}</td>
                </tr>
              </template>
            </template>
          </tbody>
        </table>
      </div>
      <div v-else class="p-6 text-center text-gray-500">No data available for the selected filters.</div>

    </div>
  </AppLayout>
</template>

<style>
/* All Tailwind classes are inline, no scoped CSS needed */
</style>
