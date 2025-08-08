<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import { formatCurrency } from "@/utils/helper";
import { computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
const props = defineProps({
  stock: {
    type: Object,
    required: true,
  },
  productTypes: {
    type: Array,
    required: true,
  },
  stockNumbers: {
    type: Array,
    required: true,
  },
});
// console.log(props);

// State for dropdowns and actions
const showDiscountDropdown = ref(false);
const showAdjustmentDropdown = ref(false);
const discountPercentage = ref(0);
const adjustmentAmount = ref(0);
const stockSearchQuery = ref("INV-" + new Date().getFullYear() + "-");
const showStockDropdown = ref(false);
// const selectedStock = ref("INV-2023-0001");
const searchProductQuery = ref("");
const stockOptions = computed(() => [...props.stockNumbers]);

// State changes:
const selectedStock = ref(props.stock?.stock_number || props.stockNumbers[0]);

// Watch for stock selection changes
watch(selectedStock, (newStockNumber) => {
  router.get(
    "pos",
    { stock_number: newStockNumber },
    {
      preserveState: true,
      preserveScroll: true,
      only: ["stock"], // Only reload the stock data
    }
  );
});

const selectedCategory = ref(null);
const filteredProducts = computed(() => {
  let items = props.stock.store_stock_items;

  if (selectedCategory.value) {
    items = items.filter(
      (item) => item.store_product.store_product_type_id === selectedCategory.value
    );
  }

  if (searchProductQuery.value.trim()) {
    const query = searchProductQuery.value.toLowerCase();
    items = items.filter((item) => item.store_product.name.toLowerCase().includes(query));
  }

  return items;
});

const cartItems = ref([]);

function addToCart(item) {
  const existing = cartItems.value.find((i) => i.id === item.id);
  if (existing) {
    existing.quantity += 1;
  } else {
    cartItems.value.push({
      id: item.id,
      product: item.store_product,
      price: item.sale_price,
      quantity: 1,
    });
  }
}

const cartNetTotal = computed(() =>
  cartItems.value.reduce((sum, i) => sum + i.price * i.quantity, 0)
);

const cartDiscount = computed(
  () => (cartNetTotal.value * discountPercentage.value) / 100
);



const cartTotal = computed(
  () => cartNetTotal.value - cartDiscount.value - adjustmentAmount.value
);

const cartAdjustment = computed(() => adjustmentAmount.value);

function applyDiscount() {
  showDiscountDropdown.value = false;
}

function applyAdjustment() {
  showAdjustmentDropdown.value = false;
}



const breadcrumbs = [
  {
    title: "POS",
    href: "/pos",
  },
];
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
          <!-- Dropdown button -->
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
            @click="showDiscountDropdown = !showDiscountDropdown"
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
            class="absolute z-10 mt-1 w-48 bg-white rounded-md shadow-lg py-1"
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
            @click="showAdjustmentDropdown = !showAdjustmentDropdown"
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
            class="absolute z-10 mt-1 w-48 bg-white rounded-md shadow-lg py-1"
          >
            <div class="px-4 py-2">
              <label class="block text-sm text-gray-700 mb-1">Adjustment Amount</label>
              <input
                v-model.number="adjustmentAmount"
                type="number"
                class="w-full border rounded px-2 py-1 text-sm"
                placeholder="Amount"
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
        <div>
          <button
            @click="showAdjustmentDropdown = !showAdjustmentDropdown"
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
            Submit Order
          </button>
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
              @click="addToCart(item)"
              class="relative rounded-lg border p-3 text-center hover:shadow-md dark:hover:shadow-md dark:shadow-blue-900 cursor-pointer"
            >
              <!-- Quantity Badge -->
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
          <div class="flex justify-between text-sm text-teal-600 mb-4">
            <a href="#" class="hover:underline">Walking Customers (Edit)</a>
            <!-- <a href="#" class="hover:underline">Select Table</a> -->
          </div>

          <!-- Cart Items -->
          <div class="flex-grow border rounded p-4 mb-4 space-y-2 overflow-y-auto">
            <div
              v-for="item in cartItems"
              :key="item.id"
              class="flex items-center justify-between border-b pb-2 text-sm space-x-2"
            >
              <!-- 🖼 Product Image -->
              <img
                :src="item.product.primary_image_url"
                alt="Product Image"
                class="w-10 h-10 object-cover rounded border"
              />

              <!-- 📦 Name & Quantity -->
              <div class="flex-1">
                <div class="font-medium">{{ item.product.name }}</div>
                <div class="text-xs text-gray-500">x{{ item.quantity }}</div>
              </div>

              <!-- 💰 Total Price -->
              <div class="font-semibold text-right">
                {{ formatCurrency(item.price * item.quantity) }}
              </div>
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
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
