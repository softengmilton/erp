<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import { formatCurrency } from "@/utils/helper";

// ======================
// 1. Props
// ======================
const props = defineProps({
  stock: { type: Object, required: true },
  productTypes: { type: Array, required: true },
  stockNumbers: { type: Array, required: true },
  customers: { type: Array, required: true },
});

// ======================
// 2. State
// ======================
const showDiscountDropdown = ref(false);
const showAdjustmentDropdown = ref(false);
const showPaymentDropdown = ref(false);
const discountPercentage = ref(0);
const adjustmentAmount = ref(0);
const paidAmount = ref(0);

const stockSearchQuery = ref("INV-" + new Date().getFullYear() + "-");
const showStockDropdown = ref(false);

const searchProductQuery = ref("");
const selectedStock = ref(props.stock?.stock_number || props.stockNumbers[0]);
const selectedCategory = ref(null);
const cartItems = ref([]);
// Loading state
const isSubmitting = ref(false);
// Payment methods
const paymentMethods = [
  { id: "cash", name: "Cash", icon: "💵" },
  { id: "bkash", name: "bKash", icon: "📱" },
  { id: "nagad", name: "Nagad", icon: "📲" },
];
const selectedPaymentMethod = ref("cash");

// ======================
// 3. Computed Properties
// ======================
const stockOptions = computed(() => [...props.stockNumbers]);

const filteredProducts = computed(() => {
  let items = props.stock.store_stock_items || [];

  // Filter by category if one is selected
  if (selectedCategory.value) {
    items = items.filter((item) => {
      return (
        String(item.store_product?.store_product_type_id) ===
        String(selectedCategory.value)
      );
    });
  }

  // Filter by search query
  if (searchProductQuery.value.trim()) {
    const query = searchProductQuery.value.toLowerCase();
    items = items.filter((item) =>
      item.store_product?.name?.toLowerCase().includes(query)
    );
  }

  return items;
});

const cartNetTotal = computed(() =>
  cartItems.value.reduce((sum, i) => sum + i.price * i.quantity, 0)
);

const cartDiscount = computed(
  () => (cartNetTotal.value * discountPercentage.value) / 100
);

const cartAdjustment = computed(() => adjustmentAmount.value);

const cartTotal = computed(
  () => cartNetTotal.value - cartDiscount.value - adjustmentAmount.value
);

const dueAmount = computed(() => {
  return Math.max(0, cartTotal.value - paidAmount.value);
});

// ======================
// 4. Watchers
// ======================
watch(selectedStock, (newStockNumber) => {
  router.get(
    "pos",
    { stock_number: newStockNumber },
    {
      preserveState: true,
      preserveScroll: true,
      only: ["stock"],
    }
  );
});

// Auto-set paid amount to total when payment method changes
watch(selectedPaymentMethod, () => {
  paidAmount.value = cartTotal.value;
});

// ======================
// 5. Methods
// ======================
// In your methods section, update these functions:

function addToCart(item) {
  if (item.current_quantity <= 0) {
    alert("This product is out of stock!");
    return;
  }

  const existing = cartItems.value.find((i) => i.id === item.id);

  if (existing) {
    // Check against current stock in the store
    const productInStock = props.stock.store_stock_items.find((p) => p.id === item.id);
    if (existing.quantity < productInStock.current_quantity) {
      existing.quantity += 1;
    } else {
      alert(`Only ${productInStock.current_quantity} items available in stock!`);
    }
  } else {
    cartItems.value.push({
      stock_number: selectedStock.value,
      id: item.id,
      product: item.store_product,
      price: item.sale_price,
      quantity: 1,
      maxStock: item.current_quantity, // Store max available quantity
    });
    console.log("Added to cart:", cartItems.value);
  }
}

function removeFromCart(itemId) {
  cartItems.value = cartItems.value.filter((item) => item.id !== itemId);
}

function increaseQuantity(itemId) {
  const item = cartItems.value.find((item) => item.id === itemId);
  if (item) {
    // Find current stock status
    const productInStock = props.stock.store_stock_items.find((p) => p.id === itemId);
    if (productInStock && item.quantity < productInStock.current_quantity) {
      item.quantity += 1;
    } else {
      alert(`Only ${productInStock.current_quantity} items available in stock!`);
    }
  }
}

function decreaseQuantity(itemId) {
  const item = cartItems.value.find((item) => item.id === itemId);
  if (item) {
    if (item.quantity > 1) {
      item.quantity -= 1;
    } else {
      removeFromCart(itemId);
    }
  }
}

function applyDiscount() {
  showDiscountDropdown.value = false;
}

function applyAdjustment() {
  showAdjustmentDropdown.value = false;
}
//
function printInvoice() {
  // Create a print window
  const printWindow = window.open('', '_blank');

  // Get the current date and time
  const now = new Date();
  const dateTime = now.toLocaleString();

  // Create invoice HTML
  let invoiceHTML = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Invoice</title>
      <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .invoice { max-width: 800px; margin: 0 auto; border: 1px solid #eee; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .info { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .totals { margin-top: 20px; float: right; width: 300px; }
        .footer { margin-top: 50px; text-align: center; color: #777; }
      </style>
    </head>
    <body>
      <div class="invoice">
        <div class="header">
          <h1>INVOICE</h1>
          <p>Date: ${dateTime}</p>
        </div>

        <div class="info">
          <div>
            <strong>Customer:</strong> ${selectedCustomer.value?.name || 'Walking Customer'}<br>
            ${selectedCustomer.value?.phone ? `<strong>Phone:</strong> ${selectedCustomer.value.phone}<br>` : ''}
            ${selectedCustomer.value?.email ? `<strong>Email:</strong> ${selectedCustomer.value.email}` : ''}
          </div>
          <div>
            <strong>Invoice #:</strong> INV-${new Date().getTime()}<br>
            <strong>Stock #:</strong> ${selectedStock.value}
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
  `;

  // Add cart items
  cartItems.value.forEach(item => {
    invoiceHTML += `
      <tr>
        <td>${item.product.name}</td>
        <td>${formatCurrency(item.price)}</td>
        <td>${item.quantity}</td>
        <td>${formatCurrency(item.price * item.quantity)}</td>
      </tr>
    `;
  });

  // Add totals
  invoiceHTML += `
          </tbody>
        </table>

        <div class="totals">
          <p><strong>Subtotal:</strong> ${formatCurrency(cartNetTotal.value)}</p>
          ${discountPercentage.value > 0 ? `<p><strong>Discount (${discountPercentage.value}%):</strong> -${formatCurrency(cartDiscount.value)}</p>` : ''}
          ${adjustmentAmount.value != 0 ? `<p><strong>Adjustment:</strong> ${formatCurrency(adjustmentAmount.value)}</p>` : ''}
          <p><strong>Total:</strong> ${formatCurrency(cartTotal.value)}</p>
          <p><strong>Paid:</strong> ${formatCurrency(paidAmount.value)}</p>
          <p><strong>Due:</strong> ${formatCurrency(dueAmount.value)}</p>
          <p><strong>Payment Method:</strong> ${paymentMethods.find(p => p.id === selectedPaymentMethod.value).name}</p>
        </div>

        <div class="footer">
          <p>Thank you for your business!</p>
        </div>
      </div>
    </body>
    </html>
  `;

  // Write the HTML to the print window
  printWindow.document.write(invoiceHTML);
  printWindow.document.close();

  // Wait for content to load before printing
  printWindow.onload = function() {
    setTimeout(() => {
      printWindow.print();
      printWindow.close();
    }, 500);
  };
}
//

function submitOrder() {
  if (cartItems.value.length === 0) return;

  isSubmitting.value = true;

  const orderData = {
    items: cartItems.value.map((item) => ({
      id: item.id,
      quantity: item.quantity,
    })),
    payment_method: selectedPaymentMethod.value,
    total: cartTotal.value,
    paid: paidAmount.value,
    due: dueAmount.value,
    discount: cartDiscount.value,
    adjustment: adjustmentAmount.value,
    stock_number: selectedStock.value,
    customer_id: selectedCustomer.value ? selectedCustomer.value.id : null,
  };
  router.post("pos", orderData, {
    preserveScroll: true,
    onSuccess: () => {
      printInvoice();
      cartItems.value = [];
      discountPercentage.value = 0;
      adjustmentAmount.value = 0;
      paidAmount.value = 0;
      selectedPaymentMethod.value = "cash";
      showPaymentDropdown.value = false;  
    },
    onError: (errors) => {
      console.error("Order submission failed:", errors);
      alert("Failed to submit order. Please try again.");
    },
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
}

// ======================
// 6. Customer
// ======================

const customerModal = ref(false);
const customerSearchQuery = ref("");
const showCustomerForm = ref(false);
const selectedCustomer = ref(null);
const customers = ref([
  { id: 0, name: "Walking Customer", phone: "", email: "", isWalking: true },
  ...props.customers,
]);

const customer = ref({
  name: "",
  phone: "",
  email: "",
});

function openCustomerModal() {
  customerModal.value = true;
  showCustomerForm.value = false;
  customerSearchQuery.value = "";
}

function closeCustomerModal() {
  customerModal.value = false;
}

function searchCustomer() {
  // Filter customers based on search query
  return customers.value.filter(
    (c) =>
      c.phone.includes(customerSearchQuery.value) ||
      c.name.toLowerCase().includes(customerSearchQuery.value.toLowerCase())
  );
}

function selectCustomer(customer) {
  selectedCustomer.value = customer;
  closeCustomerModal();
}

function showAddCustomerForm() {
  showCustomerForm.value = true;
  customer.value = { name: "", phone: "", email: "" };
}

function saveCustomer() {
  const newCustomer = {
    ...customer.value,
    id: Math.max(...customers.value.map((c) => c.id)) + 1,
    isWalking: false,
  };
  router.post("customers", newCustomer, {
    preserveScroll: true,
    onSuccess: () => {
      customers.value.push(newCustomer);
      selectCustomer(newCustomer);
      showCustomerForm.value = false;
      customer.value = { name: "", phone: "", email: "" };
    },
    onError: (errors) => {
      console.error("Failed to save customer:", errors);
      alert("Failed to save customer. Please try again.");
    },
  });
  selectCustomer(newCustomer);
}
// ======================
// 7. Constants
// ======================
const breadcrumbs = [{ title: "POS", href: "/pos" }];
</script>

<template>
  <Head title="POS" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Top Header -->
    <div
      class="w-full border-b border-slate-200 py-4 px-4 shadow-sm sticky top-0 z-10 bg-white"
    >
      <div class="flex flex-wrap items-center gap-2">
        <!-- Back Button -->
        <a
          href="#"
          class="inline-flex items-center border rounded px-4 py-2 shadow text-sm bg-white hover:bg-gray-50"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 mr-2 text-teal-600"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15 19l-7-7 7-7"
            /></svg
          >Back
        </a>
        <!-- Clear All -->
        <a
          href="#"
          @click.prevent="cartItems = []"
          class="inline-flex items-center border rounded px-4 py-2 hover:bg-white shadow text-sm text-red-500 bg-white hover:bg-gray-50"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 mr-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0v1h6V4m-6 0a1 1 0 00-1 1"
            /></svg
          >Clear
        </a>
        <!-- Orders -->
        <a
          href="#"
          class="inline-flex items-center border rounded px-4 py-2 shadow text-sm text-teal-600 bg-white hover:bg-gray-50"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 mr-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h3l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2z"
            /></svg
          >All Orders
        </a>

        <!-- Search -->
        <div class="flex-grow min-w-[150px]">
          <input
            v-model="searchProductQuery"
            type="text"
            class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 border-gray-200 shadow-sm text-sm"
            placeholder="Search products..."
          />
        </div>

        <!-- Stock Select Dropdown -->
        <div class="relative">
          <button
            id="stockDropdownButton"
            @click="showStockDropdown = !showStockDropdown"
            class="inline-flex items-center border rounded px-4 py-2 shadow text-sm bg-white hover:bg-gray-50"
            type="button"
          >
            {{ selectedStock }}
            <svg
              class="w-2.5 h-2.5 ms-3"
              aria-hidden="true"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 10 6"
            >
              <path
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="m1 1 4 4 4-4"
              />
            </svg>
          </button>

          <!-- Dropdown menu -->
          <div
            id="stockDropdown"
            v-show="showStockDropdown"
            class="z-10 absolute mt-1 bg-white rounded-lg shadow-sm w-60 dark:bg-gray-700"
          >
            <div class="p-3">
              <label for="stock-search" class="sr-only">Search</label>
              <div class="relative">
                <div
                  class="absolute inset-y-0 rtl:inset-r-0 start-0 flex items-center ps-3 pointer-events-none"
                >
                  <svg
                    class="w-4 h-4 text-gray-500 dark:text-gray-400"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 20 20"
                  >
                    <path
                      stroke="currentColor"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"
                    />
                  </svg>
                </div>
                <input
                  v-model="stockSearchQuery"
                  type="text"
                  id="stock-search"
                  class="block w-full p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                  placeholder="Search stock"
                  @click.stop
                />
              </div>
            </div>
            <ul
              class="px-3 pb-3 overflow-y-auto text-sm text-gray-700 dark:text-gray-200"
              style="max-height: 200px"
            >
              <li
                v-for="option in stockOptions.filter((o) =>
                  o.toLowerCase().includes(stockSearchQuery.toLowerCase())
                )"
                :key="option"
              >
                <div
                  class="flex items-center ps-2 rounded-sm hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer"
                  @click="
                    selectedStock = option;
                    showStockDropdown = false;
                  "
                >
                  <div
                    class="w-full py-2 text-sm font-medium text-gray-900 rounded-sm dark:text-gray-300"
                  >
                    {{ option }}
                  </div>
                </div>
              </li>
              <li
                v-if="
                  stockOptions.filter((o) =>
                    o.toLowerCase().includes(stockSearchQuery.toLowerCase())
                  ).length === 0
                "
              >
                <div class="py-2 text-sm text-gray-500 text-center">
                  No stock options found
                </div>
              </li>
            </ul>
          </div>
        </div>

        <!-- Discount Button with Dropdown -->
        <div class="relative">
          <button
            @click="
              showDiscountDropdown = !showDiscountDropdown;
              showAdjustmentDropdown = false;
              showPaymentDropdown = false;
            "
            class="inline-flex items-center border rounded px-4 py-2 shadow text-sm bg-white hover:bg-gray-50"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 mr-2 text-purple-600"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"
              />
            </svg>
            Discount
          </button>
          <div
            v-if="showDiscountDropdown"
            class="absolute z-10 mt-1 w-48 bg-white rounded-md shadow-lg py-1 border"
          >
            <div class="px-4 py-2">
              <label class="block text-sm text-gray-700 mb-1">Discount Percentage</label>
              <input
                v-model.number="discountPercentage"
                type="number"
                min="0"
                max="100"
                class="w-full border rounded px-2 py-1 text-sm"
                placeholder="0-100%"
              />
              <button
                @click="applyDiscount"
                class="mt-2 w-full bg-teal-600 text-white py-1 px-3 rounded text-sm hover:bg-teal-700"
              >
                Close
              </button>
            </div>
          </div>
        </div>

        <!-- Adjustment Button with Dropdown -->
        <div class="relative">
          <button
            @click="
              showAdjustmentDropdown = !showAdjustmentDropdown;
              showDiscountDropdown = false;
              showPaymentDropdown = false;
            "
            class="inline-flex items-center border rounded px-4 py-2 shadow text-sm bg-white hover:bg-gray-50"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 mr-2 text-blue-600"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            Adjustment
          </button>
          <div
            v-if="showAdjustmentDropdown"
            class="absolute z-10 mt-1 w-48 bg-white rounded-md shadow-lg py-1 border"
          >
            <div class="px-4 py-2">
              <label class="block text-sm text-gray-700 mb-1">Adjustment Amount</label>
              <input
                v-model.number="adjustmentAmount"
                type="number"
                class="w-full border rounded px-2 py-1 text-sm"
                placeholder="Amount"
                min="0"
              />
              <button
                @click="applyAdjustment"
                class="mt-2 w-full bg-teal-600 text-white py-1 px-3 rounded text-sm hover:bg-teal-700"
              >
                Close
              </button>
            </div>
          </div>
        </div>

        <!-- Submit Order Button with Payment Options -->
        <div class="relative">
          <button
            @click="
              showPaymentDropdown = !showPaymentDropdown;
              showDiscountDropdown = false;
              showAdjustmentDropdown = false;
            "
            class="inline-flex items-center border rounded px-4 py-2 shadow text-sm bg-teal-600 text-white hover:bg-teal-700"
            :disabled="cartItems.length === 0"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 mr-2"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              />
            </svg>
            Submit Order ({{
              paymentMethods.find((p) => p.id === selectedPaymentMethod).name
            }})
          </button>

          <!-- Payment Method Dropdown -->
          <div
            v-if="showPaymentDropdown"
            class="absolute z-10 mt-1 right-0 w-72 bg-white rounded-md shadow-lg border"
          >
            <div class="px-4 py-3 border-b">
              <h3 class="text-base font-semibold">Payment Details</h3>
            </div>
            <div class="px-4 py-3 space-y-4">
              <div>
                <h4 class="text-sm font-medium mb-2">Payment Method</h4>
                <div class="grid grid-cols-3 gap-2">
                  <button
                    v-for="method in paymentMethods"
                    :key="method.id"
                    @click="selectedPaymentMethod = method.id"
                    class="border rounded-md px-3 py-2 text-sm flex flex-col items-center"
                    :class="{
                      'border-teal-500 bg-teal-50': selectedPaymentMethod === method.id,
                    }"
                  >
                    <span class="text-lg mb-1">{{ method.icon }}</span>
                    <span>{{ method.name }}</span>
                  </button>
                </div>
              </div>
              <div class="space-y-3">
                <div>
                  <label class="block text-sm font-medium mb-1">Total Amount</label>
                  <div class="w-full border rounded-md px-3 py-2 bg-gray-50 text-sm">
                    {{ formatCurrency(cartTotal) }}
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Paid Amount</label>
                  <input
                    v-model.number="paidAmount"
                    type="number"
                    min="0"
                    :max="cartTotal"
                    class="w-full border rounded-md px-3 py-2 text-sm focus:ring-teal-500 focus:border-teal-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium mb-1">Due Amount</label>
                  <div class="w-full border rounded-md px-3 py-2 bg-gray-50 text-sm">
                    {{ formatCurrency(dueAmount) }}
                  </div>
                </div>
              </div>
              <button
                @click="submitOrder"
                :disabled="cartItems.length === 0 || isSubmitting"
                class="w-full bg-teal-600 text-white py-2 px-4 rounded-md text-sm font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="!isSubmitting">Confirm Order</span>
                <span v-else class="flex items-center justify-center">
                  <svg
                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    ></circle>
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  Processing...
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Layout -->
    <div class="w-full px-4 py-4">
      <div class="grid grid-cols-12 gap-4 h-[calc(100vh-96px)]">
        <!-- Sidebar: Scrollable (col-2) -->
        <div
          class="col-span-4 lg:col-span-1 overflow-y-auto rounded shadow-inner border p-2 space-y-2"
        >
          <a
            href="#"
            v-for="type in props.productTypes"
            :key="type.id"
            @click.prevent="
              selectedCategory = selectedCategory === type.id ? null : type.id
            "
            :class="[
              'flex flex-col items-center hover:bg-gray-100 dark:hover:bg-gray-900 p-2 border rounded shadow-sm',
              selectedCategory === type.id ? 'bg-teal-50 border-teal-500' : '',
            ]"
          >
            <img
              :src="type.primary_image_url"
              class="w-12 h-12 object-cover rounded-full mb-1"
            />
            <span class="text-xs font-medium text-center">{{ type.name }}</span>
          </a>
        </div>

        <!-- Products Grid: 6 per row (col-6) -->
        <div class="col-span-8 lg:col-span-8 overflow-y-auto">
          <div
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3"
          >
            <!-- Product -->
            <div
              v-for="item in filteredProducts"
              :key="item.id"
              @click="item.current_quantity > 0 ? addToCart(item) : null"
              class="relative rounded-lg border p-3 text-center"
              :class="{
                'opacity-50 cursor-not-allowed': item.current_quantity <= 0,
                'hover:shadow-md cursor-pointer': item.current_quantity > 0,
              }"
            >
              <!-- Stock Badge -->
              <div
                class="absolute -top-2 -right-2 px-2 py-1 rounded-full text-xs font-bold shadow-sm"
                :class="{
                  'bg-green-100 text-green-800': item.current_quantity > 0,
                  'bg-red-100 text-red-800': item.current_quantity <= 0,
                }"
              >
                <span v-if="item.current_quantity > 0"
                  >{{ item.current_quantity }} in stock</span
                >
                <span v-else>Out of stock</span>
              </div>

              <img
                :src="item.store_product.primary_image_url"
                alt="Product"
                class="mb-2 mx-auto max-w-full h-20 object-cover rounded"
                :class="{ grayscale: item.current_quantity <= 0 }"
              />

              <div class="font-medium text-sm">{{ item.store_product.name }}</div>
              <div class="text-teal-600 text-sm font-semibold">
                {{ formatCurrency(item.sale_price) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Cart Sidebar (col-4) -->
        <div
          class="col-span-12 lg:col-span-3 flex flex-col border light:border-slate-200 rounded shadow-md p-4"
        >
          <!-- Customer Info -->
          <!-- Customer Info -->
          <!-- Customer Info -->
          <div class="flex justify-between text-sm text-teal-600 mb-4">
            <a href="#" class="hover:underline" @click="openCustomerModal">
              {{ selectedCustomer ? selectedCustomer.name : customers[0].name }} (Edit)
            </a>
          </div>
          <!--  customer modal -->
          <!-- Customer modal -->
          <div
            v-if="customerModal"
            class="fixed inset-0 flex items-start justify-center z-50 p-4 pt-20"
            role="dialog"
            aria-modal="true"
          >
            <!-- Glass overlay -->
            <div class="fixed inset-0 backdrop-blur-sm" @click="closeCustomerModal"></div>

            <!-- Modal content -->
            <div
              class="relative bg-white rounded-lg p-6 w-full max-w-xl shadow-xl border border-gray-200 overflow-y-auto max-h-[calc(100vh-10rem)]"
            >
              <h2 class="text-xl font-semibold mb-4 text-gray-900">
                {{ showCustomerForm ? "Add Customer" : "Select Customer" }}
              </h2>

              <!-- Customer List View -->
              <div v-if="!showCustomerForm">
                <!-- Search section -->
                <div class="mb-6">
                  <h3 class="text-md font-medium mb-2 text-gray-700">
                    Search customer by phone or name
                  </h3>
                  <div class="flex gap-2">
                    <input
                      type="text"
                      v-model="customerSearchQuery"
                      placeholder="Enter phone or name"
                      class="flex-1 border rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 border-gray-200 shadow-sm text-sm"
                      @keyup.enter="searchCustomer"
                    />
                    <button
                      @click="searchCustomer"
                      class="px-3 py-2 rounded-md text-sm font-medium shadow-sm bg-teal-600 text-white hover:bg-teal-700"
                    >
                      Search
                    </button>
                  </div>
                </div>

                <!-- Customer list -->
                <div class="space-y-2 max-h-60 overflow-y-auto">
                  <!-- Walking Customer option -->
                  <div
                    class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 rounded"
                    @click="selectCustomer(customers[0])"
                  >
                    <div class="font-medium">Walking Customer</div>
                    <div class="text-sm text-gray-600">No phone</div>
                  </div>

                  <!-- Other customers -->
                  <div
                    v-for="cust in searchCustomer().filter((c) => c.id !== 0)"
                    :key="cust.id"
                    class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 rounded"
                    @click="selectCustomer(cust)"
                  >
                    <div class="font-medium">{{ cust.name }}</div>
                    <div class="text-sm text-gray-600">{{ cust.phone }}</div>
                    <div class="text-xs text-gray-500">{{ cust.email }}</div>
                  </div>

                  <div
                    v-if="searchCustomer().length === 0"
                    class="text-center py-4 text-gray-500"
                  >
                    No customers found
                  </div>
                </div>

                <button
                  @click="showAddCustomerForm"
                  class="w-full mt-4 px-4 py-2 rounded-md text-sm font-medium shadow-sm bg-teal-600 text-white hover:bg-teal-700"
                >
                  Add New Customer
                </button>
              </div>

              <!-- Customer Form View -->
              <div v-else class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Name *</label
                  >
                  <input
                    type="text"
                    v-model="customer.name"
                    placeholder="Customer name"
                    required
                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 border-gray-200 shadow-sm text-sm"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Phone *</label
                  >
                  <input
                    type="text"
                    v-model="customer.phone"
                    placeholder="Customer phone"
                    required
                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 border-gray-200 shadow-sm text-sm"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Email</label
                  >
                  <input
                    type="email"
                    v-model="customer.email"
                    placeholder="Customer email"
                    class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-teal-500 border-gray-200 shadow-sm text-sm"
                  />
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                  <button
                    @click="showCustomerForm = false"
                    class="px-4 py-2 rounded-md border text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50"
                  >
                    Back
                  </button>
                  <button
                    @click="saveCustomer"
                    :disabled="!customer.name || !customer.phone"
                    class="px-5 py-2 rounded-md text-sm font-medium shadow-sm bg-teal-600 text-white hover:bg-teal-700 disabled:opacity-50"
                  >
                    Save Customer
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Cart Items -->
          <div class="flex-grow border rounded p-4 mb-4 space-y-2 overflow-y-auto">
            <div
              v-for="item in cartItems"
              :key="item.id"
              class="flex items-center justify-between border-b pb-2 text-sm space-x-2"
            >
              <!-- Product Image -->
              <img
                :src="item.product.primary_image_url"
                alt="Product Image"
                class="w-10 h-10 object-cover rounded border"
              />

              <!-- Name & Quantity Controls -->
              <div class="flex-1">
                <div class="font-medium">{{ item.product.name }}</div>
                <div class="flex items-center mt-1 space-x-2">
                  <button
                    @click.stop="decreaseQuantity(item.id)"
                    class="w-6 h-6 flex items-center justify-center border rounded text-gray-600 hover:bg-gray-100 hover:text-teal-600 transition-colors"
                  >
                    -
                  </button>
                  <span class="text-sm">{{ item.quantity }}</span>
                  <button
                    @click.stop="increaseQuantity(item.id)"
                    class="w-6 h-6 flex items-center justify-center border rounded text-gray-600 hover:bg-gray-100 hover:text-teal-600 transition-colors"
                  >
                    +
                  </button>
                </div>
              </div>

              <!-- Price and Remove Button -->
              <div class="flex flex-col items-end">
                <div class="font-semibold">
                  {{ formatCurrency(item.price * item.quantity) }}
                </div>
                <button
                  @click.stop="removeFromCart(item.id)"
                  class="mt-1 text-xs text-red-500 hover:text-red-700 hover:underline"
                >
                  Remove
                </button>
              </div>
            </div>

            <div v-if="cartItems.length === 0" class="text-center py-4 text-gray-500">
              No items in cart
            </div>
          </div>

          <!-- Totals -->
          <div class="space-y-2 text-sm">
            <div class="flex justify-between border p-2 rounded">
              <span class="font-medium">Net:</span>
              <span>{{ formatCurrency(cartNetTotal) }}</span>
            </div>
            <div class="flex justify-between border p-2 rounded">
              <span class="font-medium">Discount ({{ discountPercentage }}%):</span>
              <span>-{{ formatCurrency(cartDiscount) }}</span>
            </div>
            <div class="flex justify-between border p-2 rounded">
              <span class="font-medium">Adjustment:</span>
              <span>{{ formatCurrency(cartAdjustment) }}</span>
            </div>
            <div
              class="flex justify-between border p-2 rounded bg-teal-100 dark:text-gray-900 font-semibold"
            >
              <span>Total:</span>
              <span>{{ formatCurrency(cartTotal) }}</span>
            </div>

            <!-- Payment Summary -->
            <div class="mt-4 pt-4 border-t">
              <div class="flex justify-between p-2 rounded bg-blue-50">
                <span class="font-medium">Paid:</span>
                <span>{{ formatCurrency(paidAmount) }}</span>
              </div>
              <div
                class="flex justify-between p-2 rounded"
                :class="{ 'bg-green-50': dueAmount === 0, 'bg-yellow-50': dueAmount > 0 }"
              >
                <span class="font-medium">Due:</span>
                <span
                  :class="{
                    'text-green-600': dueAmount === 0,
                    'text-yellow-600': dueAmount > 0,
                  }"
                >
                  {{ formatCurrency(dueAmount) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
