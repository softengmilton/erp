<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Order',
        href: '/dashboard',
    },
];
</script>

<template>
  <Head title="Orders" />

  <AppLayout :breadcrumbs="breadcrumbs">


    <div class="p-6">
      <div class="mb-6 gap-4 sm:flex sm:items-center sm:justify-between">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">
          My orders
        </h2>
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
          <input
            type="text"
            placeholder="Search orders..."
            class="sm:w-64 px-4 py-2 border rounded-md bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          />
        </div>
        <div
          class="mt-6 gap-4 space-y-4 sm:mt-0 sm:flex sm:items-center sm:justify-end sm:space-y-0"
        >
        <div>
          <label
            for="order-type"
            class="sr-only mb-2 block text-sm font-medium text-gray-900 dark:text-white"
            >Select order type</label
          >
          <select
            id="order-type"
            class="block w-full min-w-[8rem] rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
          >
            <option selected>All orders</option>
            <option value="pre-order">Pre-order</option>
            <option value="transit">In transit</option>
            <option value="confirmed">Confirmed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <span class="inline-block text-gray-500 dark:text-gray-400"> from </span>

        <div>
            <label
              for="duration"
              class="sr-only mb-2 block text-sm font-medium text-gray-900 dark:text-white"
              >Select duration</label
            >
            <select
              id="duration"
              class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500"
            >
              <option selected>this week</option>
              <option value="this month">this month</option>
              <option value="last 3 months">the last 3 months</option>
              <option value="lats 6 months">the last 6 months</option>
              <option value="this year">this year</option>
            </select>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto border rounded-lg border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Order Id:
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Date:
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Price:
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Status:
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                <a href="#" class="hover:underline">#FWB125467980</a>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                11.12.2023
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                $499
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                <dd
                  class="me-2 mt-1.5 inline-flex items-center rounded bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                >
                  <svg
                    class="me-1 h-3 w-3"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 7h6l2 4m-8-4v8m0-8V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v9h2m8 0H9m4 0h2m4 0h2v-4m0 0h-5m3.5 5.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Zm-10 0a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"
                    />
                  </svg>
                  In transit
                </dd>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3"
              >
                  
                <button
                  type="button"
                  class="w-full rounded-lg border border-red-700 px-3 py-2 text-center text-sm font-medium text-red-700 hover:bg-red-700 hover:text-white focus:outline-none focus:ring-4 focus:ring-red-300 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-600 dark:hover:text-white dark:focus:ring-red-900 lg:w-auto"
                >
                  Cancel order
                </button>
                <a
                  href="/store/order-details"
                  class="w-full inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700 lg:w-auto"
                  >View details</a
                >
              </td>
            </tr>
            <tr
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                <a href="#" class="hover:underline">#FWB125467980</a>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                11.12.2023
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                $499
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                <dd
                  class="me-2 mt-1.5 inline-flex items-center rounded bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300"
                >
                  <svg
                    class="me-1 h-3 w-3"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 11.917 9.724 16.5 19 7.5"
                    />
                  </svg>
                  Confirmed
                </dd>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3"
              >
                  
                <button
                  type="button"
                  class="w-full rounded-lg border border-red-700 px-3 py-2 text-center text-sm font-medium text-red-700 hover:bg-red-700 hover:text-white focus:outline-none focus:ring-4 focus:ring-red-300 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-600 dark:hover:text-white dark:focus:ring-red-900 lg:w-auto"
                >
                  Cancel order
                </button>
                <a
                  href="/store/order-details"
                  class="w-full inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700 lg:w-auto"
                  >View details</a
                >
              </td>
            </tr>
            <tr
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                <a href="#" class="hover:underline">#FWB125467980</a>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                11.12.2023
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                $499
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                <dd
                    class="me-2 mt-1.5 inline-flex items-center rounded bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300"
                  >
                    <svg
                      class="me-1 h-3 w-3"
                      aria-hidden="true"
                      xmlns="http://www.w3.org/2000/svg"
                      width="24"
                      height="24"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke="currentColor"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18 17.94 6M18 18 6.06 6"
                      />
                    </svg>
                    Cancelled
                  </dd>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3"
              >
                  
                <button
                  type="button"
                  class="w-full rounded-lg border border-red-700 px-3 py-2 text-center text-sm font-medium text-red-700 hover:bg-red-700 hover:text-white focus:outline-none focus:ring-4 focus:ring-red-300 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-600 dark:hover:text-white dark:focus:ring-red-900 lg:w-auto"
                >
                  Cancel order
                </button>
                <a
                  href="/store/order-details"
                  class="w-full inline-flex justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700 lg:w-auto"
                  >View details</a
                >
              </td>
            </tr>
            <tr>
              <td colspan="7" class="px-6 py-6 text-center text-gray-500">
                No orders found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
