<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3'

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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <input
            type="text"
            placeholder="Search orders..."
            class="sm:w-64 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
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
              class="block w-full min-w-[8rem] rounded-lg border p-2.5 text-sm"
            >
              <option selected>All orders</option>
              <option value="pre-order">Pre-order</option>
              <option value="transit">In transit</option>
              <option value="confirmed">Confirmed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <span class="inline-block"> from </span>

          <div>
            <label for="duration" class="sr-only mb-2 block text-sm font-medium"
              >Select duration</label
            >
            <select id="duration" class="block w-full rounded-lg border p-2.5 text-sm">
              <option selected>this week</option>
              <option value="this month">this month</option>
              <option value="last 3 months">the last 3 months</option>
              <option value="lats 6 months">the last 6 months</option>
              <option value="this year">this year</option>
            </select>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full divide-y">
          <thead>
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
              >
                Order Id:
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
              >
                Date:
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
              >
                Price:
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
              >
                Status:
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr class="transition-colors duration-150">
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <a href="#" class="hover:underline">#FWB125467980</a>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">11.12.2023</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">$499</td>
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
                <a
                  href="javascript:void(0)"
                  class="w-full rounded-lg border cursor border-red-200 px-3 py-2 text-center text-sm font-medium lg:w-auto"
                >
                  Cancel order
                </a>
                <Link
                  :href="`/store/order/1`"
                  class="w-full inline-flex justify-center rounded-lg border px-3 py-2 text-sm font-medium lg:w-auto"
                >
                  View details
                </Link>
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
