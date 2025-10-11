<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    productTypes: Array,
    products: Array,
    tableData: Array,
    grands : Object,
  });

// console.log(props.tableData);
// console.log(props.grands);
// console.log(props.productTypes);

const selectedCategory = ref('');
const selectedProduct = ref('');

const filterData = ref({
  category_id: null,
  product_id: null,
});

// Watch category id
watch(selectedCategory, (newCatId) => {
  filterData.value.category_id = newCatId || null;
  // console.log(newCatId);
  applyRange();
});

// Watch product id
watch(selectedProduct, (newProductId) => {
  filterData.value.product_id = newProductId || null;
  // console.log(newProductId);
  applyRange();

});

function applyRange() {
  const payload = {};

  // only send params that are not null
  if (filterData.value.category_id) payload.category_id = filterData.value.category_id;
  if (filterData.value.product_id) payload.product_id = filterData.value.product_id;

    console.log('Sending payload:', payload); // 👈 add this line

  router.get("/store/stock-product-reports", payload, {
    preserveState: true,
    replace: true,

    onSuccess: () => {

    },
  });
}

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

      
    <!-- Product Type Selector -->
    <div class="min-w-[200px]">
      <select
        id="category"
        v-model="selectedCategory"
        class="w-full border border-gray-300 rounded p-2"
      >
        <option value="">Select Product Types</option>
        <option
          v-for="type in props.productTypes"
          :key="type.id"
          :value="type.id"
        >
          {{ type.name }}
        </option>
      </select>
    </div>
    <!-- Product Selector -->
    <div class="min-w-[200px]">
      <select
        id="category"
        v-model="selectedProduct"
        class="w-full border border-gray-300 rounded p-2"
      >
        <option value="">Select Product</option>
        <option
          v-for="product in props.products"
          :key="product.id"
          :value="product.id"
        >
          {{ product.name }}
        </option>
      </select>
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
              <td class="border px-4 py-2">Sold - Buy Product Price</td>
              <td class="border px-4 py-2">Sold Product Price</td>
              <td class="border px-4 py-2">Total Profit/Product</td>
              
              <td class="border px-4 py-2">Initial Stock</td>
              <td class="border px-4 py-2">Availble Stock</td>

              <td class="border px-4 py-2">Availble Stock (Buy Price)</td>
              <td class="border px-4 py-2">Availble Stock (Sale Price)</td>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in props.tableData" :key="index">
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
              <td class="border px-4 py-2">{{ item.sold_buy_product_price  }}</td>
              <td class="border px-4 py-2">{{ item.sold_product_price  }}</td>
              <td class="border px-4 py-2">{{ item.total_profit_product  }}</td>
              
              <td class="border px-4 py-2">{{ item.quantity }}</td>
              <td class="border px-4 py-2">{{ item.availble_stock  }}</td>
              <td class="border px-4 py-2">{{ item.availble_asset_buy_price  }}</td>
              <td class="border px-4 py-2">{{ item.availble_asset_sale_price  }}</td>
            </tr>
            <tr>
              <td class="border px-4 py-2">Grand Total</td>
              <td class="border px-4 py-2"></td>
              <td class="border px-4 py-2"></td>
              <td class="border px-4 py-2"></td>
              <td class="border px-4 py-2"></td>
              <td class="border px-4 py-2"></td>
              <td class="border px-4 py-2"></td>
              <td class="border px-4 py-2"></td>
              <td class="border px-4 py-2"></td>
              <td class="border px-4 py-2">{{ grands.grand_buy_price_asset }}</td>
              <td class="border px-4 py-2">{{ grands.grand_sale_price_asset }}</td>
              <td class="border px-4 py-2">{{ grands.grand_sold_product }}</td>
              <td class="border px-4 py-2">{{ grands.grand_sold_buy_product_price }}</td>
              <td class="border px-4 py-2">{{ grands.grand_sold_product_price }}</td>
              <td class="border px-4 py-2">{{ grands.grand_profit_product }}</td>
              <td class="border px-4 py-2">{{ grands.grand_initial_stock }}</td>
              <td class="border px-4 py-2">{{ grands.grand_avaiable_stock }}</td>
              <td class="border px-4 py-2">{{ grands.grand_availble_asset_buy_price }}</td>
              <td class="border px-4 py-2">{{ grands.grand_availble_asset_sale_price }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- <div  class="p-6 text-center text-gray-500">No data available for the selected filters.</div> -->

    </div>
  </AppLayout>
</template>

