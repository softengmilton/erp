<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PosSidebar from '@/components/PosSidebar.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'POS',
        href: '/pos',
    },
];
</script>

<template>
    <Head title="POS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        


         <!-- Top Header -->
  <div class="w-full border-b border-slate-200 py-4 px-4 shadow-sm bg-white sticky top-0 z-10">
    <div class="flex flex-wrap items-center gap-2">
      <!-- Back Button -->
      <a href="#" class="inline-flex items-center border rounded px-4 py-2 bg-white hover:bg-gray-100 shadow text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>Back
      </a>
      <!-- Clear All -->
      <a href="#" class="inline-flex items-center border rounded px-4 py-2 bg-white hover:bg-gray-100 shadow text-sm text-red-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0v1h6V4m-6 0a1 1 0 00-1 1" />
        </svg>Clear
      </a>
      <!-- Orders -->
      <a href="#" class="inline-flex items-center border rounded px-4 py-2 bg-white hover:bg-gray-100 shadow text-sm text-teal-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h3l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2z" />
        </svg>All Orders
      </a>
      <!-- Dropdown -->
      <select class="border rounded px-3 py-2 bg-white text-slate-800 shadow text-sm">
        <option>Order Type</option>
        <option>Dine In</option>
        <option>Takeaway</option>
        <option>Delivery</option>
      </select>
      <!-- Search -->
      <div class="flex-grow min-w-[150px]">
        <input type="text" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 shadow-sm text-sm" placeholder="Search..." />
      </div>
    </div>
  </div>

  <!-- Main Layout -->
  <div class="w-full px-4 py-4">
    <div class="flex gap-4 h-[calc(100vh-96px)]">

      <!-- Sidebar: Scrollable -->
         <PosSidebar class="w-44 overflow-y-auto bg-white rounded shadow-inner border border-gray-200 p-2 space-y-2" />

      <!-- Products Grid: 6 per row -->
      <div class="flex-grow overflow-y-auto">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
          <!-- Product -->
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Cheese Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$8.00</div>
          </div>
          <!-- Repeat Product -->
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <div class="bg-white rounded-lg border border-gray-200 p-3 text-center hover:shadow-md cursor-pointer">
            <img src="img/burger.jpg" alt="Product" class="mb-2 mx-auto max-w-full h-20 object-cover rounded" />
            <div class="font-medium text-sm">Mutton Burger</div>
            <div class="text-teal-600 text-sm font-semibold">$9.50</div>
          </div>
          <!-- Add more product cards here -->
        </div>
      </div>

      <!-- Cart Sidebar -->
      <div class="w-80 flex flex-col bg-white border border-slate-200 rounded shadow-md p-4">
        <!-- Customer Info -->
        <div class="flex justify-between text-sm text-teal-600 mb-4">
          <a href="#" class="hover:underline">Walking Customers (Edit)</a>
          <a href="#" class="hover:underline">Select Table</a>
        </div>

        <!-- Cart Items -->
        <div class="flex-grow bg-gray-50 rounded p-4 mb-4 flex items-center justify-center text-slate-400 text-sm">
          Cart is empty
        </div>

        <!-- Totals -->
        <div class="space-y-2 text-sm">
          <div class="flex justify-between border p-2 rounded">
            <span class="font-medium">Net:</span>
            <span>$0.00</span>
          </div>
          <div class="flex justify-between border p-2 rounded">
            <span class="font-medium">Discount (0%):</span>
            <span>$0.00</span>
          </div>
          <div class="flex justify-between border p-2 rounded">
            <span class="font-medium">Tax:</span>
            <span>$0.00</span>
          </div>
          <div class="flex justify-between border p-2 rounded bg-teal-100 font-semibold">
            <span>Total:</span>
            <span>$0.00</span>
          </div>
        </div>
      </div>

    </div>
  </div>



    </AppLayout>
</template>
