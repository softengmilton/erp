<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    order: Object
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '/store/orders' },
    { title: `Order #${props.order.order_number}`, href: '#' },
];

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadge = (status) => {
    const statusMap = {
        'paid': { bg: 'bg-green-100', text: 'text-green-800', label: 'Paid' },
        'due': { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Due' },
    };

    return statusMap[status] || { bg: 'bg-gray-100', text: 'text-gray-800', label: status };
};

const getPaymentMethodIcon = (method) => {
    const icons = {
        'cash': '💰',
        'card': '💳',
        'bank': '🏦',
        'bkash': '📱',
        'Nagad': '📱',
    };
    return icons[method] || '⚪';
};

const subtotal = computed(() => {
    return props.order.store_order_items.reduce((sum, item) => {
        return sum + (item.sale_price * item.quantity);
    }, 0);
});

const total = computed(() => {
    return subtotal.value - props.order.discount + props.order.adjustment;
});
</script>

<template>
  <Head :title="`Order #${order.order_number}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <section class="py-4 antialiased md:py-8 print:py-2">
      <div class="mx-auto max-w-screen-xl px-4 2xl:px-0 print:max-w-full print:px-2">
        <div class="mx-auto max-w-3xl print:max-w-full">
          <!-- Receipt Header -->
          <div class="mb-6 text-center print:mb-4">
            <h1 class="text-2xl font-bold  sm:text-3xl print:text-xl">
              Your Business Name
            </h1>
            <p class="text-sm print:text-xs">
              123 Business Street, City, Country<br>
              Phone: (123) 456-7890 | Email: info@business.com
            </p>
          </div>

          <!-- Order Info -->
          <div class="mb-6 rounded-lg border  p-4 print:mb-4 print:p-2 print:text-sm">
            <div class="flex items-center justify-between border-b pb-3 print:pb-1">
              <div>
                <h2 class="text-lg font-semibold text-gray-900 print:text-base">
                  Order #{{ order.order_number }}
                </h2>
                <p class="text-sm  print:text-xs">
                  {{ formatDate(order.created_at) }}
                </p>
              </div>
              <span :class="`inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ${getStatusBadge(order.payment_status).bg} ${getStatusBadge(order.payment_status).text} print:px-2`">
                {{ getStatusBadge(order.payment_status).label }}
              </span>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2 print:mt-1">
              <div>
                <h3 class="text-sm font-medium print:text-xs">Customer</h3>
                <p class="text-sm print:text-xs">
                  <template v-if="order.customer_type === 'registered' && order.customer">
                    {{ order.customer.name }}
                  </template>
                  <template v-else>
                    Walking Customer
                  </template>
                </p>
              </div>
              <div>
                <h3 class="text-sm font-medium print:text-xs">Contact</h3>
                <p class="text-sm print:text-xs">
                  <template v-if="order.customer_type === 'registered' && order.customer && order.customer.phone">
                    {{ order.customer.phone }}
                  </template>
                  <template v-else>
                    N/A
                  </template>
                </p>
              </div>
              <div>
                <h3 class="text-sm font-medium print:text-xs">Payment Method</h3>
                <p class="text-sm text-gray-900 dark:text-white print:text-xs">
                  <span class="inline-flex items-center gap-1">
                    {{ getPaymentMethodIcon(order.payment_method) }}
                    {{ order.payment_method }}
                  </span>
                </p>
              </div>
              <div>
                <h3 class="text-sm font-medium print:text-xs">Staff</h3>
                <p class="text-sm text-gray-900 dark:text-white print:text-xs">
                  <!-- {{ order.user.name }} -->
                </p>
              </div>
            </div>
          </div>

          <!-- Order Items -->
          <div class="mb-6 overflow-hidden rounded-lg border print:mb-4">
            <table class="min-w-full divide-y">
              <thead class="">
                <tr>
                  <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider print:px-2 print:py-1 print:text-xs">
                    Item
                  </th>
                  <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider print:px-2 print:py-1 print:text-xs">
                    Price
                  </th>
                  <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider print:px-2 print:py-1 print:text-xs">
                    Qty
                  </th>
                  <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider print:px-2 print:py-1 print:text-xs">
                    Total
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr v-for="item in order.store_order_items" :key="item.id">
                  <td class="whitespace-nowrap px-4 py-3 text-sm print:px-2 print:py-1 print:text-xs">
                    <div class="flex items-center">
                      <div class="ml-0">
                        <div class="font-medium">{{ item.store_product.name }}</div>
                        <div v-if="item.store_product.barcode" class="text-xs print:text-2xs">
                          {{ item.store_product.barcode }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="whitespace-nowrap px-4 py-3 text-right text-sm print:px-2 print:py-1 print:text-xs">
                    ${{ item.sale_price.toFixed(2) }}
                  </td>
                  <td class="whitespace-nowrap px-4 py-3 text-right text-sm print:px-2 print:py-1 print:text-xs">
                    {{ item.quantity }}
                  </td>
                  <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium  print:px-2 print:py-1 print:text-xs">
                    ${{ (item.sale_price * item.quantity).toFixed(2) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Order Summary -->
          <div class="rounded-lg border p-4 print:p-2">
            <h3 class="mb-3 text-lg font-semibold print:mb-1 print:text-base">
              Order Summary
            </h3>

            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm  print:text-xs">Subtotal</span>
                <span class="text-sm  print:text-xs">${{ subtotal.toFixed(2) }}</span>
              </div>

              <div class="flex justify-between">
                <span class="text-sm  print:text-xs">Discount</span>
                <span class="text-sm text-red-500 print:text-xs">- ${{ order.discount.toFixed(2) }}</span>
              </div>

              <div class="flex justify-between">
                <span class="text-sm print:text-xs">Adjustment</span>
                <span class="text-sm print:text-xs">+ ${{ order.adjustment.toFixed(2) }}</span>
              </div>

              <div class="flex justify-between border-t pt-2 print:pt-1">
                <span class="text-base font-bold print:text-sm">Total</span>
                <span class="text-base font-bold print:text-sm">${{ total.toFixed(2) }}</span>
              </div>

              <div class="flex justify-between">
                <span class="text-sm print:text-xs">Amount Paid</span>
                <span class="text-sm print:text-xs">${{ order.paid_amount.toFixed(2) }}</span>
              </div>

              <div v-if="order.payment_status === 'due'" class="flex justify-between">
                <span class="text-sm print:text-xs">Amount Due</span>
                <span class="text-sm font-medium text-red-500 print:text-xs">${{ order.due_amount.toFixed(2) }}</span>
              </div>
            </div>
          </div>

          <!-- Receipt Footer -->
          <div class="mt-6 text-center text-xs text-gray-500 print:mt-4 print:text-2xs">
            <p>Thank you for your business!</p>
            <p class="mt-1">Please retain this receipt for your records</p>
            <p class="mt-1">For returns or exchanges, present receipt within 14 days</p>
          </div>

          <!-- Action Buttons -->
          <div class="mt-8 flex flex-col gap-4 print:hidden sm:flex-row">
            <button
              @click="window.print()"
              class="rounded-lg border  px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:border-gray-700 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
            >
              Print Receipt
            </button>

            <Link
              href="/store/orders"
              class="rounded-lg border px-5 py-2.5 text-center text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:border-gray-700 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
            >
              Back to Orders
            </Link>

            <template v-if="order.payment_status === 'due'">
              <Link
                :href="`/store/orders/${order.id}/payment`"
                class="rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
              >
                Record Payment
              </Link>
            </template>
          </div>
        </div>
      </div>
    </section>
  </AppLayout>
</template>
