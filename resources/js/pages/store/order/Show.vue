<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import StoreSetting, { initStoreSetting } from "@/utils/module/StoreSetting";

// Initialize store settings
initStoreSetting();

const props = defineProps({
  order: Object,
});

const breadcrumbs = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Orders", href: "/store/orders" },
  { title: `Order #${props.order.order_number}`, href: "#" },
];

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const getStatusBadge = (status) => {
  const statusMap = {
    paid: { bg: "bg-green-100", text: "text-green-800", label: "Paid" },
    due: { bg: "bg-yellow-100", text: "text-yellow-800", label: "Due" },
  };
  return statusMap[status] || { bg: "bg-gray-100", text: "text-gray-800", label: status };
};

const getPaymentMethodIcon = (method) => {
  const icons = {
    cash: "💰",
    card: "💳",
    bank: "🏦",
    bkash: "📱",
    Nagad: "📱",
  };
  return icons[method] || "⚪";
};

const subtotal = computed(() => {
  return props.order.store_order_items.reduce((sum, item) => {
    return sum + (Number(item.sale_price) || 0) * (Number(item.quantity) || 0);
  }, 0);
});

const total = computed(() => {
  return (
    subtotal.value -
    (Number(props.order.discount) || 0) +
    (Number(props.order.adjustment) || 0)
  );
});

// Safe money formatting
const formatMoney = (value) => Number(value || 0).toFixed(2);

// Print invoice using store settings
const printInvoice = () => {
  const now = new Date();
  const dateTime = now.toLocaleString();
  const settings = StoreSetting.all.value;

  const printWindow = window.open("", "_blank");
  let invoiceHTML = `
    <!DOCTYPE html>
    <html>
      <head>
        <title>Invoice #${props.order.order_number}</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
          .invoice { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ccc; box-sizing: border-box; }
          .header { text-align: center; margin-bottom: 20px; }
          .header img { max-height: 60px; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto; }
          .header h1 { margin: 0; font-size: 22px; font-weight: bold; color: #222; }
          .info { display: flex; justify-content: space-between; flex-wrap: wrap; margin-bottom: 20px; font-size: 13px; }
          .info div { margin-bottom: 10px; }
          .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px; }
          .table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: center; }
          .table th { background: #f9f9f9; font-weight: bold; }
          .totals { width: 300px; margin-left: auto; border-collapse: collapse; }
          .totals td { border: 1px solid #ccc; padding: 8px; }
          .totals td:first-child { font-weight: bold; background: #f9f9f9; }
          .footer { text-align: center; font-size: 12px; color: #666; margin-top: 20px; }
        </style>
      </head>
      <body>
        <div class="invoice">
          <div class="header">
            <h1>${settings.business_title || "Store Name"}</h1>
            <p>${settings.address || ""}</p>
            <p>${settings.business_email || ""} | ${settings.phone || ""}</p>
            <p>Date: ${dateTime}</p>
          </div>

          <div class="info">
            <div>
              <strong>Customer:</strong> ${
                props.order.customer_type === "registered" && props.order.customer
                  ? props.order.customer.name
                  : "Walking Customer"
              }<br>
              ${
                props.order.customer?.phone
                  ? `<strong>Phone:</strong> ${props.order.customer.phone}`
                  : ""
              }
            </div>
            <div>
              <strong>Invoice #:</strong> INV-${props.order.order_number}<br>
              <strong>Order #:</strong> ${props.order.order_number}
            </div>
          </div>

          <table class="table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              ${props.order.store_order_items
                .map(
                  (item) => `
                <tr>
                  <td>${item.store_product.name}</td>
                  <td>${formatMoney(item.sale_price)}</td>
                  <td>${Number(item.quantity) || 0}</td>
                  <td>${formatMoney(
                    (Number(item.sale_price) || 0) * (Number(item.quantity) || 0)
                  )}</td>
                </tr>
              `
                )
                .join("")}
            </tbody>
          </table>

          <table class="totals">
            <tr><td>Subtotal:</td><td>${formatMoney(subtotal.value)}</td></tr>
            <tr><td>Discount:</td><td>-${formatMoney(props.order.discount)}</td></tr>
            <tr><td>Adjustment:</td><td>${formatMoney(props.order.adjustment)}</td></tr>
            <tr><td>Total:</td><td>${formatMoney(total.value)}</td></tr>
            <tr><td>Paid:</td><td>${formatMoney(props.order.paid_amount)}</td></tr>
            <tr><td>Due:</td><td>${
              props.order.payment_status === "due"
                ? formatMoney(props.order.due_amount)
                : "0.00"
            }</td></tr>
            <tr><td>Payment Method:</td><td>${props.order.payment_method}</td></tr>
          </table>

          <div class="footer">
            <p>Thank you for your business!</p>
            <p style="margin-top:8px; font-size:11px; color:#999;">
                Powered by <a href="https://infinityflamesoft.com/" target="_blank" style="color:#0d9488; text-decoration:none;">Infinity Flame Soft</a>
            </p>
        </div>
        </div>
      </body>
    </html>
  `;

  printWindow.document.write(invoiceHTML);
  printWindow.document.close();
  printWindow.onload = () =>
    setTimeout(() => {
      printWindow.print();
      printWindow.close();
    }, 500);
};
</script>

<template>
  <Head :title="`Order #${order.order_number}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <section class="py-4 antialiased md:py-8 print:py-2">
      <div class="mx-auto max-w-screen-xl px-4 2xl:px-0 print:max-w-full print:px-2">
        <div class="mx-auto max-w-3xl print:max-w-full">
          <!-- Receipt Header -->
          <div class="mb-6 text-center print:mb-4">
            <div v-if="StoreSetting.all.value.logo">
              <img
                :src="`/storage/${StoreSetting.all.value.logo}`"
                alt="Logo"
                class="mx-auto mb-2 max-h-16"
              />
            </div>
            <h1 class="text-2xl font-bold sm:text-3xl print:text-xl">
              {{ StoreSetting.all.value.business_title || "Store Name" }}
            </h1>
            <p class="text-sm print:text-xs">
              {{ StoreSetting.all.value.address || "Address not set" }}<br />
              {{ StoreSetting.all.value.phone || "Phone not set" }}
              <span v-if="StoreSetting.all.value.business_email">
                | {{ StoreSetting.all.value.business_email }}</span
              >
            </p>
          </div>

          <!-- Order Info -->
          <div class="mb-6 rounded-lg border p-4 print:mb-4 print:p-2 print:text-sm">
            <div class="flex items-center justify-between border-b pb-3 print:pb-1">
              <div>
                <h2 class="text-lg font-semibold text-gray-900 print:text-base">
                  Order #{{ order.order_number }}
                </h2>
                <p class="text-sm print:text-xs">
                  {{ formatDate(order.created_at) }}
                </p>
              </div>
              <span
                :class="`inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ${
                  getStatusBadge(order.payment_status).bg
                } ${getStatusBadge(order.payment_status).text} print:px-2`"
              >
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
                  <template v-else> Walking Customer </template>
                </p>
              </div>
              <div>
                <h3 class="text-sm font-medium print:text-xs">Contact</h3>
                <p class="text-sm print:text-xs">
                  <template
                    v-if="
                      order.customer_type === 'registered' &&
                      order.customer &&
                      order.customer.phone
                    "
                  >
                    {{ order.customer.phone }}
                  </template>
                  <template v-else> N/A </template>
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
              <thead>
                <tr>
                  <th
                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider print:px-2 print:py-1 print:text-xs"
                  >
                    Item
                  </th>
                  <th
                    class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider print:px-2 print:py-1 print:text-xs"
                  >
                    Price
                  </th>
                  <th
                    class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider print:px-2 print:py-1 print:text-xs"
                  >
                    Qty
                  </th>
                  <th
                    class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider print:px-2 print:py-1 print:text-xs"
                  >
                    Total
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y">
                <tr v-for="item in order.store_order_items" :key="item.id">
                  <td
                    class="whitespace-nowrap px-4 py-3 text-sm print:px-2 print:py-1 print:text-xs"
                  >
                    <div class="flex items-center">
                      <div class="ml-0">
                        <div class="font-medium">{{ item.store_product.name }}</div>
                        <div
                          v-if="item.store_product.barcode"
                          class="text-xs print:text-2xs"
                        >
                          {{ item.store_product.barcode }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td
                    class="whitespace-nowrap px-4 py-3 text-right text-sm print:px-2 print:py-1 print:text-xs"
                  >
                    ${{ formatMoney(item.sale_price) }}
                  </td>
                  <td
                    class="whitespace-nowrap px-4 py-3 text-right text-sm print:px-2 print:py-1 print:text-xs"
                  >
                    {{ Number(item.quantity) || 0 }}
                  </td>
                  <td
                    class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium print:px-2 print:py-1 print:text-xs"
                  >
                    ${{
                      formatMoney(
                        (Number(item.sale_price) || 0) * (Number(item.quantity) || 0)
                      )
                    }}
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
                <span class="text-sm print:text-xs">Subtotal</span>
                <span class="text-sm print:text-xs"
                  >${{ formatMoney(subtotal.value) }}</span
                >
              </div>
              <div class="flex justify-between">
                <span class="text-sm print:text-xs">Discount</span>
                <span class="text-sm text-red-500 print:text-xs"
                  >- ${{ formatMoney(order.discount) }}</span
                >
              </div>
              <div class="flex justify-between">
                <span class="text-sm print:text-xs">Adjustment</span>
                <span class="text-sm print:text-xs"
                  >+ ${{ formatMoney(order.adjustment) }}</span
                >
              </div>
              <div class="flex justify-between border-t pt-2 print:pt-1">
                <span class="text-base font-bold print:text-sm">Total</span>
                <span class="text-base font-bold print:text-sm"
                  >${{ formatMoney(total.value) }}</span
                >
              </div>
              <div class="flex justify-between">
                <span class="text-sm print:text-xs">Amount Paid</span>
                <span class="text-sm print:text-xs"
                  >${{ formatMoney(order.paid_amount) }}</span
                >
              </div>
              <div v-if="order.payment_status === 'due'" class="flex justify-between">
                <span class="text-sm print:text-xs">Amount Due</span>
                <span class="text-sm font-medium text-red-500 print:text-xs"
                  >${{ formatMoney(order.due_amount) }}</span
                >
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
              @click="printInvoice"
              class="rounded-lg border px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:border-gray-700 dark:hover:bg-gray-700 dark:focus:ring-gray-700"
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
