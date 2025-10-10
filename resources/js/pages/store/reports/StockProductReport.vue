<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

  const props = defineProps({
    tableData: Array,
  });

console.log(props.tableData);


// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Stock Product Reports', href: '/store/stock-product-reports' },
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
      </div>

      <!-- Report Table -->
      <div class="overflow-x-auto" ref="tableRef">
        <table class="min-w-full border-collapse border border-black font-mono text-sm">
          <thead class="bg-gray-100">
            <tr>
              <td class="border px-10 py-2">Invoice Number</td>
              <td class="border px-4 py-2">Product Name</td>
              <td class="border px-4 py-2">Unit Cost</td>
              <td class="border px-4 py-2">Shipping Cost</td>
              <td class="border px-4 py-2">Other Cost</td>
              <td class="border px-4 py-2">Fees</td>
              <td class="border px-4 py-2">Costing Per Product</td>
              <td class="border px-4 py-2">Sale Price</td>
              <td class="border px-4 py-2">Profit Per Product</td>
              <td class="border px-4 py-2">Buy Price Asset</td>
              <td class="border px-4 py-2">Sale Price Asset</td>
              <td class="border px-4 py-2">Sold Product</td>
              <td class="border px-4 py-2">Sold Product Price</td>
              <td class="border px-4 py-2">Total Profit/Product</td>
              
              <td class="border px-4 py-2">Initial Stock</td>
              <td class="border px-4 py-2">Availble Stock</td>

              <td class="border px-4 py-2">Availble Stock (Buy Price)</td>
              <td class="border px-4 py-2">Availble Stock (Sale Price)</td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in tableData" :key="index">
              <td class="border px-4 py-2">{{ item.invoice_number }}</td>
              <td class="border px-4 py-2">{{ item.product_name }}</td>
              <td class="border px-4 py-2">{{ item.unit_cost }}</td>
              <td class="border px-4 py-2">{{ item.shipping }}</td>
              <td class="border px-4 py-2">{{ item.fees }}</td>
              <td class="border px-4 py-2">{{ item.fees }}</td>
              <td class="border px-4 py-2">{{ item.costing_per_product }}</td>
              <td class="border px-4 py-2">{{ item.sale_price }}</td>
              <td class="border px-4 py-2">{{ item.profit_per_product  }}</td>
              <td class="border px-4 py-2">{{ item.buy_price_asset  }}</td>
              <td class="border px-4 py-2">{{ item.sale_price_asset  }}</td>
              <td class="border px-4 py-2">{{ item.sold_product  }}</td>
              <td class="border px-4 py-2">{{ item.sold_product_price  }}</td>
              <td class="border px-4 py-2">{{ item.total_profit_product  }}</td>
              
              <td class="border px-4 py-2">{{ item.quantity }}</td>
              <td class="border px-4 py-2">{{ item.availble_stock  }}</td>
              <td class="border px-4 py-2">{{ item.availble_asset_buy_price  }}</td>
              <td class="border px-4 py-2">{{ item.availble_asset_sale_price  }}</td>
            </tr>
            <!-- <tr v-for="item in stockSummary" :key="item.product_id">
              <td class="border px-4 py-2">{{ item.product_name }}</td>
              <td class="border px-4 py-2">{{ item.total_purchased }}</td>
              <td class="border px-4 py-2">{{ item.total_sold }}</td>
              <td class="border px-4 py-2">{{ item.available_stock }}</td>
            </tr> -->
          </tbody>
        </table>
      </div>
      <!-- <div  class="p-6 text-center text-gray-500">No data available for the selected filters.</div> -->

    </div>
  </AppLayout>
</template>

