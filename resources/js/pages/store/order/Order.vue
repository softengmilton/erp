<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import debounce from "lodash/debounce";

const props = defineProps({
  orders: Object,
  filters: Object,
});

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
const formatMoney = (value) =>
  Number(value || 0).toFixed(2);
const statusOptions = [
  { value: "", label: "All Statuses" },
  { value: "paid", label: "Paid" },
  { value: "due", label: "Due" },
];

const paymentMethodOptions = [
  { value: "", label: "All Methods" },
  { value: "cash", label: "Cash" },
  { value: "card", label: "Card" },
  { value: "bank", label: "Bank Transfer" },
  { value: "bkash", label: "bKash" },
  { value: "Nagad", label: "Nagad" },
];

const customerTypeOptions = [
  { value: "", label: "All Types" },
  { value: "walking", label: "Walking Customer" },
  { value: "registered", label: "Registered Customer" },
];

const search = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status || "");
const paymentMethodFilter = ref(props.filters.payment_method || "");
const customerTypeFilter = ref(props.filters.customer_type || "");
const dateRange = ref(props.filters.date_range || "this_week");

const dateRangeOptions = [
  { value: "today", label: "Today" },
  { value: "this_week", label: "This Week" },
  { value: "this_month", label: "This Month" },
  { value: "last_3_months", label: "Last 3 Months" },
  { value: "last_6_months", label: "Last 6 Months" },
  { value: "this_year", label: "This Year" },
  { value: "custom", label: "Custom Range" },
];

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

const cancelOrder = (orderId) => {
  if (confirm("Are you sure you want to cancel this order?")) {
    router.delete(`/store/orders/${orderId}`, {
      preserveScroll: true,
      onSuccess: () => {},
    });
  }
};

const applyFilters = debounce(() => {
  router.get(
    "/store/orders",
    {
      search: search.value,
      status: statusFilter.value,
      payment_method: paymentMethodFilter.value,
      customer_type: customerTypeFilter.value,
      date_range: dateRange.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
}, 500);

watch(
  [search, statusFilter, paymentMethodFilter, customerTypeFilter, dateRange],
  applyFilters
);

const isModalOpen = ref(false);
const isEditing = ref(false);
const paymentUpdate = ref({
  id: null,
  due_amount: "",
});

function openEditModal(order) {
  paymentUpdate.value = { ...order };
  isModalOpen.value = true;
  isEditing.value = true;
  console.log(paymentUpdate.value.id);
  console.log(paymentUpdate.value.due_amount);
}

function closeModal() {
  isModalOpen.value = false;
}

function updatePayment() {
  if (isEditing.value) {
    router.put(`/store/orders/${paymentUpdate.value.id}`, paymentUpdate.value, {
      onSuccess: () => closeModal(),
    });
  }
}

const breadcrumbs = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Orders", href: "/store/orders" },
];
</script>

<template>
  <Head title="Orders" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="mb-6 gap-4 sm:flex sm:items-center sm:justify-between">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <input
            v-model="search"
            type="text"
            placeholder="Search by order number..."
            class="sm:w-64 px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          />
        </div>
        <div
          class="mt-6 gap-4 space-y-4 sm:mt-0 sm:flex sm:items-center sm:justify-end sm:space-y-0"
        >
          <div>
            <select
              v-model="statusFilter"
              class="block w-full min-w-[8rem] rounded-lg border p-2.5 text-sm"
            >
              <option v-for="option in statusOptions" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>

          <div>
            <select
              v-model="paymentMethodFilter"
              class="block w-full min-w-[8rem] rounded-lg border p-2.5 text-sm"
            >
              <option v-for="option in paymentMethodOptions" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>

          <div>
            <select
              v-model="customerTypeFilter"
              class="block w-full min-w-[8rem] rounded-lg border p-2.5 text-sm"
            >
              <option v-for="option in customerTypeOptions" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>

          <div>
            <select
              v-model="dateRange"
              class="block w-full rounded-lg border p-2.5 text-sm"
            >
              <option v-for="option in dateRangeOptions" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full divide-y">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Order #
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Date
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Customer
              </th>
              <!-- <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Items
              </th> -->
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Total
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Paid
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Due
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Payment
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Status
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <template v-if="orders.data.length > 0">
              <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ order.order_number }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(order.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <span v-if="order.customer_type === 'walking'">Walking Customer</span>
                  <span v-else-if="order.customer">{{ order.customer.name }}</span>
                  <span v-else>N/A</span>
                </td>
                <!-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ order.storeOrderItems?.length || 0 }}
                </td> -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                   ${{ formatMoney(order.total_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${{ formatMoney(order.paid_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${{ formatMoney(order.due_amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <span class="inline-flex items-center gap-1">
                    {{ getPaymentMethodIcon(order.payment_method) }}
                    {{ order.payment_method }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span
                    :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                      getStatusBadge(order.payment_status).bg
                    } ${getStatusBadge(order.payment_status).text}`"
                  >
                    {{ getStatusBadge(order.payment_status).label }}
                  </span>
                </td>
                <td
                  class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2"
                >
                  <Link
                    :href="`/store/orders/${order.id}`"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    View
                  </Link>
                  <button
                    v-if="order.payment_status === 'due'"
                    @click="cancelOrder(order.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Cancel
                  </button>
                  <button
                    v-if="
                      order.payment_status == 'due' || order.payment_status == 'partial'
                    "
                    @click="openEditModal(order)"
                    class="text-teal-600"
                  >
                    confirm payment
                  </button>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td colspan="10" class="px-6 py-6 text-center text-gray-500">
                  No orders found matching your criteria.
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex items-center justify-between" v-if="orders.data.length > 0">
        <div class="text-sm text-gray-700">
          Showing {{ orders.from }} to {{ orders.to }} of {{ orders.total }} results
        </div>
        <div class="flex space-x-1">
          <Link
            v-for="link in orders.links"
            :key="link.label"
            :href="link.url || '#'"
            class="px-3 py-1 rounded-md border"
            :class="{
              'bg-blue-500 text-white border-blue-500': link.active,
              'text-gray-700 hover:bg-gray-50': !link.active && link.url,
              'text-gray-300 cursor-not-allowed': !link.url,
            }"
            preserve-scroll
          >
            <span v-html="link.label"></span>
          </Link>
        </div>
      </div>
    </div>
    <div>
      <!-- Add Modal For Payment -->
      <div
        v-if="isModalOpen"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
      >
        <!-- Glass overlay -->
        <div class="fixed inset-0 backdrop-blur-sm"></div>

        <!-- Modal content -->
        <div
          class="relative backdrop-blur-md rounded-lg p-6 w-full max-w-md shadow-xl border"
        >
          <h2 class="text-xl font-semibold mb-4">Due payment</h2>

          <label class="block mb-4">
            <span class="block text-sm font-medium mb-1">Amount</span>
            <input
              v-model="paymentUpdate.due_amount"
              type="number"
              placeholder="Enter amount"
              required
              class="mt-1 block w-full rounded-md shadow-sm px-3 py-2 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            />
          </label>

          <div class="flex justify-end space-x-3">
            <button
              @click="closeModal"
              class="px-4 py-2 rounded-md border text-sm font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              @click="updatePayment"
              class="px-5 py-2 rounded-md text-sm font-medium shadow-sm bg-gray-800 text-white hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
