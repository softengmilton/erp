<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  stocks: {
    type: Object,
    required: true,
  },
});

const searchQuery = ref('');
const isDeleting = ref(false);
const selectedStock = ref(null);

const openDeleteConfirm = (stock) => {
  selectedStock.value = stock;
  isDeleting.value = true;
};

const closeDeleteConfirm = () => {
  selectedStock.value = null;
  isDeleting.value = false;
};

const deleteStock = () => {
  // Placeholder: integrate delete logic (e.g., Inertia.delete)
  console.log('Deleting stock:', selectedStock.value);
  closeDeleteConfirm();
};

const goToPage = (url) => {
  if (url) window.location.href = url;
};

const filteredStocks = computed(() => {
  return props.stocks.data.filter((stock) =>  // Fixed: use props.stocks instead of stocks
    stock.invoice_number.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const breadcrumbs = [
  { title: 'Stocks', href: '/store/stocks' },
];
</script>

<template>
  <Head title="Stocks" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Top Bar -->
      <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by Invoice Number..."
          class="sm:w-64 px-4 py-2 border rounded-md bg-white text-gray-900 placeholder-gray-500
                 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
        />
        <Link
            href="/store/stocks/create"
          class="inline-flex items-center justify-center px-5 py-2 bg-gray-700 text-white font-semibold rounded-md shadow-sm
                 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
        >
          + Add Stock
        </Link>
      </div>

      <!-- Stocks Table -->
      <div class="overflow-x-auto border rounded-lg border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Invoice #</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Supplier</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Total Cost</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Total Units</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sold Units</th>
              <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="stock in filteredStocks"
              :key="stock.id"
              class="hover:bg-gray-50 transition"
            >
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            <div class="flex items-center gap-3">
                <img
                :src="stock.image_path || '/assets/default/default_invoice.png'"
                alt="Invoice Image"
                class="h-10 w-10 rounded object-cover"
                />
                <span>{{ stock.invoice_number }}</span>
            </div>
            </td>

              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ stock.supplier_name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                ${{ stock.total_cost.toFixed(2) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ stock.total_quantity }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ stock.total_movements }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                <button
                  class="text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 rounded"
                >
                  Edit
                </button>
                <button
                  @click="openDeleteConfirm(stock)"
                  class="text-red-600 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 rounded"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="filteredStocks.length === 0">
              <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                No stocks found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav class="mt-6 flex justify-center space-x-2" aria-label="Pagination">
        <button
          v-for="link in props.stocks.links"
          :key="link.label"
          :disabled="!link.url"
          @click.prevent="goToPage(link.url)"
          class="px-4 py-2 border rounded-md text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50
                 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition"
          :class="{ 'bg-blue-50 border-blue-500 text-blue-600': link.active }"
          v-html="link.label"
        ></button>
      </nav>

      <!-- Delete Confirmation Modal -->
      <div
        v-if="isDeleting"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
      >
        <!-- Glass overlay -->
        <div
          class="fixed inset-0 backdrop-blur-sm"
          @click="closeDeleteConfirm"
        ></div>

        <!-- Modal content -->
        <div class="relative bg-white/90 backdrop-blur-md rounded-lg p-6 w-full max-w-sm shadow-xl border border-white/20">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">
            Confirm Delete
          </h3>
          <p class="mb-6 text-gray-700">
            Are you sure you want to delete stock
            <strong class="text-gray-900">{{ selectedStock?.invoice_number }}</strong>?
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="closeDeleteConfirm"
              class="px-4 py-2 rounded-md border text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
            >
              Cancel
            </button>
            <button
              @click="deleteStock"
              class="px-5 py-2 rounded-md text-sm font-medium shadow-sm bg-red-600 text-white hover:bg-red-700
                     focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
