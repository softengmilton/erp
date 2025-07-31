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
      <!-- Summary Section -->
      <div class="flex justify-start items-start mb-6 gap-14">
        <div>
          <div class="uppercase text-sm font-semibold">Total Asset Value</div>
          <div class="text-4xl font-bold  mb-6">{{ totalAssetValue }}</div>
           <div>
            <div class="uppercase text-sm font-semibold">Invoice Number</div>
            <div class="text-xl font-semibold ">#INV-123456</div>
        </div>
        </div>
        <div class="text-left border-l  pl-6">
          <div class="text-sm">{{ totalProducts }} product</div>
          <div class="flex space-x-4 text-sm mt-2">
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
          <!-- Status Bar -->
          <div class="w-full rounded-full h-2.5 mt-3 relative overflow-hidden">
            <div class="bg-green-500 h-2.5 absolute left-0" :style="{ width: `${(inStock / totalProducts) * 100}%` }"></div>
            <div class="bg-yellow-500 h-2.5 absolute" :style="{ width: `${(lowStock / totalProducts) * 100}%`, left: `${(inStock / totalProducts) * 100}%` }"></div>
            <div class="bg-red-500 h-2.5 absolute right-0" :style="{ width: `${(outOfStock / totalProducts) * 100}%`, left: `${((inStock + lowStock) / totalProducts) * 100}%` }"></div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-4">
        <nav class="flex space-x-6" aria-label="Tabs">
          <a href="#" class="border-b-2  py-2 px-1 text-sm font-medium">Inventory</a>
        </nav>
      </div>

      <!-- Controls -->
      <div class="flex justify-between items-center mb-4">
        <input
          type="text"
          placeholder="Search name or prescription ID..."
          class="border px-3 py-2 rounded-md w-1/3"
        />
        <div class="flex gap-2">
          <button class="border border-gray-300 px-4 py-2 rounded-md text-sm">Filters</button>
          <button class="border border-gray-300 px-4 py-2 rounded-md text-sm">New Price</button>
          <button class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm">+ Return</button>
        </div>
      </div>

      <!-- Product Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left border-t">
          <thead class="uppercase text-xs">
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
          <tbody class="divide-y">
            <tr
              v-for="product in products"
              :key="product.sku"
              class="hover:bg-gray-50 hover:dark:bg-gray-900 transition"
            >
              <td class="px-4 py-3">{{ product.name }}</td>
              <td class="px-4 py-3">{{ product.category }}</td>
              <td class="px-4 py-3">{{ product.sku }}</td>
              <td class="px-4 py-3">{{ product.vendor }}</td>
              <td class="px-4 py-3">{{ product.stock }}</td>
              <td class="px-4 py-3">
                <span
                  :class="{
                    'text-green-600 font-medium': product.status === 'IN STOCK',
                    'text-yellow-600 font-medium': product.status === 'LOW STOCK',
                    'text-red-600 font-medium': product.status === 'OUT OF STOCK',
                  }"
                >
                  {{ product.status }}
                </span>
              </td>
              <td class="px-4 py-3">{{ product.value }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
