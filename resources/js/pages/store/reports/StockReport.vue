<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
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
};

// Watch filters → request new report
watch([selectedCategory, selectedMonth], ([newCat, newMonth]) => {
    filterData.value.category_id = newCat;
    filterData.value.month = newMonth;
    applyRange();
});

function applyRange() {
    router.get('/store/stock-reports', filterData.value, {
        preserveState: true,
        replace: true,
    });
}

// CSV
function exportTableToCSV() {
    const table = document.querySelector('table');
    if (!table) return;

    const rows = Array.from(table.querySelectorAll('tr'));
    const matrix = []; // 2D array that will represent the table fully expanded

    rows.forEach((row, r) => {
        matrix[r] = matrix[r] || [];
        const cells = Array.from(row.querySelectorAll('th, td'));
        let col = 0;

        for (const cell of cells) {
            // find next free column in this row
            while (matrix[r][col] !== undefined) col++;

            const text = (cell.innerText || '').trim().replace(/\r?\n|\r/g, ' ');
            const rowspan = parseInt(cell.getAttribute('rowspan') || '1', 10);
            const colspan = parseInt(cell.getAttribute('colspan') || '1', 10);

            // fill all covered cells (repeat the text so CSV columns align exactly)
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
            const cell = row && row[c] !== undefined ? row[c] : '';
            // escape quotes properly
            cells.push(`"${String(cell).replace(/"/g, '""')}"`);
        }
        return cells.join(',');
    });

    // Add BOM so Excel detects UTF-8 correctly
    const csvString = '\uFEFF' + csvRows.join('\n');

    const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', 'stock_report.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// ✅ Ref for table
const tableRef = ref(null);

// ✅ Print function
const printTable = () => {
    const printContent = tableRef.value.innerHTML;
    const printWindow = window.open('', '', 'width=900,height=650');

    printWindow.document.write(`
    <html>
      <head>
        <title>Summary Report - ${window.StoreSetting?.get('business_title') || 'Business'}</title>
        <style>
          body { font-family: sans-serif; padding: 20px; }
          h1, h2, h3 { margin: 0; padding: 0; }
          .header { text-align: center; margin-bottom: 20px; }
          .header img { max-height: 80px; margin-bottom: 10px; }
          table { border-collapse: collapse; width: 100%; font-size: 12px; }
          th, td { border: 1px solid #000; padding: 6px; text-align: center; }
          th { background: #f4f4f4; }
          .report-title { display: none; }
        </style>
      </head>
      <body>
        <div class="header">
          ${window.StoreSetting?.get('logo') ? `<img src="${window.StoreSetting.get('logo')}" alt="Logo" />` : ''}
          <h1>${window.StoreSetting?.get('business_title') || 'Business'}</h1>
          <p>${window.StoreSetting?.get('address') || ''}</p>
          <p>${window.StoreSetting?.get('business_email') || ''} | ${window.StoreSetting?.get('phone') || ''}</p>
          <h3>Summary Report: ${props.dateLabel || ''}</h3>
        </div>
        ${printContent}
      </body>
    </html>
  `);

    printWindow.document.close();
    printWindow.print();
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Stock Reports', href: '/store/stock-reports' },
];
</script>

<template>
    <Head title="Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Export Buttons + Filter Toggle -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex gap-2">
                        <button
                            @click="exportTableToCSV"
                            class="flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 shadow-sm hover:bg-gray-50"
                        >
                            📄 Export CSV
                        </button>

                        <button
                            @click="printTable"
                            class="flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 shadow-sm hover:bg-gray-50"
                        >
                            🖨 Print
                        </button>
                        <!-- <button
                            @click="adjustMonth"
                            class="flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 shadow-sm hover:bg-gray-50"
                        >
                            🖨 Adjust Month
                        </button> -->
                    </div>
                </div>

                <!-- Filters -->
                <div v-if="filterToggle" class="rounded-lg bg-gray-50 p-4 shadow transition-all duration-300">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="min-w-[260px]">
                            <select v-model="selectedMonth" class="w-full rounded border border-gray-300 p-2">
                                <option value="">Select Month</option>
                                <option v-for="(monthName, index) in months" :key="index" :value="index + 1">
                                    {{ monthName }}
                                </option>
                            </select>
                        </div>

                        <div class="min-w-[200px]">
                            <select id="category" v-model="selectedCategory" class="w-full rounded border border-gray-300 p-2">
                                <option value="">All Categories</option>
                                <option v-for="category in props.allCategory" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Section -->
            <div class="p-6">
                <div class="overflow-x-auto" ref="tableRef">
                    <table class="min-w-full border-collapse border border-black font-mono text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-black px-4 py-2 text-left">Month</th>
                                <th class="border border-black px-4 py-2 text-left">Product Name</th>
                                <!-- <th class="border border-black px-4 py-2 text-left">Stock Number</th> -->
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
                                            <!-- <td class="border border-black px-4 py-2">
                        {{ row.stock_number }}
                      </td> -->
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
