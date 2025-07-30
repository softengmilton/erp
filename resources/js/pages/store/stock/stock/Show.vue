<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const products = ref([
  { name: 'Anbesol', category: 'Pain and Anxiety', sku: 'ZKSB124', vendor: 'Barone LLC.', stock: 0, status: 'OUT OF STOCK', value: '$0' },
  { name: 'Anesthesia', category: 'Pain and Anxiety', sku: 'ZKSB123', vendor: 'Dentalku', stock: 124, status: 'IN STOCK', value: '$2,000' },
  { name: 'Doxycycline', category: 'Periodontal Disease', sku: 'ZKSB122', vendor: 'Dentalku', stock: 0, status: 'IN STOCK', value: '$1,500' },
  { name: 'Lidex', category: 'Anti-inflammatory', sku: 'ZKSB121', vendor: 'Barone LLC.', stock: 0, status: 'OUT OF STOCK', value: '$0' },
  { name: 'Orabase', category: 'Anti-inflammatory', sku: 'ZKSB120', vendor: 'Barone LLC.', stock: 10, status: 'LOW STOCK', value: '$1,800' },
  { name: 'Orajel', category: 'Pain and Anxiety', sku: 'ZKSB119', vendor: 'K24', stock: 10, status: 'IN STOCK', value: '$4,200' },
  { name: 'PerioChip', category: 'Plaque and Gingivitis', sku: 'ZKSB118', vendor: 'K24', stock: 8, status: 'IN STOCK', value: '$5,200' },
  { name: 'Peridex', category: 'Plaque and Gingivitis', sku: 'ZKSB117', vendor: 'K24', stock: 10, status: 'LOW STOCK', value: '$800' },
  { name: 'Temovate', category: 'Anti-inflammatory', sku: 'ZKSB116', vendor: 'Dentalku', stock: 124, status: 'IN STOCK', value: '$823' },
]);

const totalAssetValue = '$10,200,323';
const totalProducts = 32;
const inStock = 21;
const lowStock = 21;
const outOfStock = 21;
</script>

<template>
  <Head title="Dashboard" />
  <AppLayout>
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <div class="text-gray-500 uppercase text-sm font-semibold">Total Asset Value</div>
          <div class="text-4xl font-bold text-gray-800">{{ totalAssetValue }}</div>
        </div>
        <div class="text-right">
          <div class="text-sm text-gray-500">{{ totalProducts }} product</div>
          <div class="flex space-x-3 text-sm mt-1">
            <div class="flex items-center space-x-1">
              <span class="w-3 h-3 bg-green-500 rounded-full"></span>
              <span class="text-green-600">In stock: {{ inStock }}</span>
            </div>
            <div class="flex items-center space-x-1">
              <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
              <span class="text-yellow-600">Low stock: {{ lowStock }}</span>
            </div>
            <div class="flex items-center space-x-1">
              <span class="w-3 h-3 bg-red-500 rounded-full"></span>
              <span class="text-red-600">Out of stock: {{ outOfStock }}</span>
            </div>
          </div>
          <!-- Progress Bar -->
          <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
            <div class="bg-green-500 h-2.5 rounded-l-full" :style="{ width: `${(inStock / totalProducts) * 100}%` }"></div>
            <div class="bg-yellow-500 h-2.5" :style="{ width: `${(lowStock / totalProducts) * 100}%`, marginLeft: `${(inStock / totalProducts) * 100}%` }"></div>
            <div class="bg-red-500 h-2.5 rounded-r-full" :style="{ width: `${(outOfStock / totalProducts) * 100}%`, marginLeft: `${((inStock + lowStock) / totalProducts) * 100}%` }"></div>
          </div>
        </div>
      </div>

      <div class="border-b border-gray-200 mb-4">
        <nav class="flex space-x-6" aria-label="Tabs">
          <a href="#" class="text-blue-600 border-b-2 border-blue-600 py-2 px-1 text-sm font-medium">Inventory</a>
          <a href="#" class="text-gray-500 hover:text-gray-700 py-2 px-1 text-sm font-medium">Order Stock</a>
        </nav>
      </div>

      <div class="flex justify-between mb-4 items-center">
        <input type="text" placeholder="Search name or prescription ID..." class="border border-gray-300 px-3 py-2 rounded-md w-1/3" />
        <!-- <div class="flex space-x-2">
          <button class="border border-gray-300 px-4 py-2 rounded-md text-sm">Filters</button>
          <button class="border border-gray-300 px-4 py-2 rounded-md text-sm">Order Stock</button>
          <button class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm">+ New Product</button>
        </div> -->
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left border-t border-gray-200">
          <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
            <tr>
              <th class="px-4 py-2">Name</th>
              <th class="px-4 py-2">Categories</th>
              <th class="px-4 py-2">SKU</th>
              <th class="px-4 py-2">Vendor</th>
              <th class="px-4 py-2">Stock</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Asset Value</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="product in products" :key="product.sku" class="hover:bg-gray-50">
              <td class="px-4 py-3">{{ product.name }}</td>
              <td class="px-4 py-3">{{ product.category }}</td>
              <td class="px-4 py-3">{{ product.sku }}</td>
              <td class="px-4 py-3">{{ product.vendor }}</td>
              <td class="px-4 py-3">{{ product.stock }}</td>
              <td class="px-4 py-3">
                <span :class="{
                  'text-green-600 font-medium': product.status === 'IN STOCK',
                  'text-yellow-600 font-medium': product.status === 'LOW STOCK',
                  'text-red-600 font-medium': product.status === 'OUT OF STOCK',
                }">{{ product.status }}</span>
              </td>
              <td class="px-4 py-3">{{ product.value }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
