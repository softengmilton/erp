<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head,router } from "@inertiajs/vue3";
import { ref } from "vue";
import { formatCurrency, formatDate } from "@/utils/helper.js";

const props = defineProps({
  stock: {
    type: Object,
    required: true,
  },
  stats: {
    type: Object,
    required: true,
  },
});
console.log("Stock Details Page Loaded", props.stock, props.stats);

// Modal states
const showPriceModal = ref(false);
const showAdjustmentModal = ref(false);
const selectedItem = ref(null);

// Form data
const priceForm = ref({
  sale_price: 0,
  note: "",
});

const adjustmentForm = ref({
  type: "damage",
  quantity: 1,
  note: "",
});

// Open modals with item data
const openPriceModal = (item) => {
  selectedItem.value = item;
  priceForm.value = {
    sale_price: item.sale_price,
    note: "",
  };
  showPriceModal.value = true;
};

console.log("Selected Item for Price Update:", selectedItem.value);
const openAdjustmentModal = (item) => {
  selectedItem.value = item;
  adjustmentForm.value = {
    type: "damage",
    quantity: 1,
    note: "",
  };
  showAdjustmentModal.value = true;
};

// Submit handlers
const submitPriceUpdate = () => {
   router.post(`/store/stocks/${props.stock.id}/update-price/${selectedItem.value.store_product_id}`, {
       sale_price: priceForm.value.sale_price,
       note: priceForm.value.note,
  });
  showPriceModal.value = false;
};

const submitAdjustment = () => {
  console.log("Submitting adjustment for:", selectedItem.value.id, adjustmentForm.value);
  // TODO: Add your API call here
  showAdjustmentModal.value = false;
};
</script>

<template>
  <Head title="Stock Details" />
  <AppLayout>
    <div class="p-6">
      <!-- Summary Section -->
      <div
        class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-6"
      >
        <!-- Left Summary -->
        <div
          class="flex flex-col sm:flex-row justify-start items-start gap-6 sm:gap-14 w-full lg:w-auto"
        >
          <div class="w-full sm:w-auto">
            <div class="uppercase text-sm font-semibold">Total Asset Value</div>
            <div class="text-3xl sm:text-4xl font-bold mb-4 sm:mb-6">
              {{ formatCurrency(stock.total_sale) }}
            </div>
            <div>
              <div class="uppercase text-sm font-semibold">Invoice Number</div>
              <div class="text-lg sm:text-xl font-semibold">
                #{{ stock.invoice_number }}
              </div>
            </div>
          </div>
          <div class="text-left border-l pl-4 sm:pl-6 w-full sm:w-auto">
            <div class="text-sm">{{ stats.totalProducts }} products</div>
            <div class="flex flex-wrap gap-2 sm:gap-4 text-sm mt-2">
              <div class="flex items-center space-x-1">
                <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                <span class="text-green-600">In stock: {{ stats.inStock }}</span>
              </div>
              <div class="flex items-center space-x-1">
                <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                <span class="text-yellow-600">Low stock: {{ stats.lowStock }}</span>
              </div>
              <div class="flex items-center space-x-1">
                <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                <span class="text-red-600">Out of stock: {{ stats.outOfStock }}</span>
              </div>
            </div>
            <!-- Status Bar -->
            <div class="w-full rounded-full h-2.5 mt-3 relative overflow-hidden">
              <div
                class="bg-green-500 h-2.5 absolute left-0"
                :style="{ width: `${(stats.inStock / stats.totalProducts) * 100}%` }"
              ></div>
              <div
                class="bg-yellow-500 h-2.5 absolute"
                :style="{
                  width: `${(stats.lowStock / stats.totalProducts) * 100}%`,
                  left: `${(stats.inStock / stats.totalProducts) * 100}%`,
                }"
              ></div>
              <div
                class="bg-red-500 h-2.5 absolute right-0"
                :style="{
                  width: `${(stats.outOfStock / stats.totalProducts) * 100}%`,
                  left: `${
                    ((stats.inStock + stats.lowStock) / stats.totalProducts) * 100
                  }%`,
                }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Right Cost Cards -->
        <div class="p-4 rounded-lg border w-full lg:w-auto">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <!-- Cost Cards -->
            <div class="space-y-3">
              <div
                class="flex items-center justify-between p-2 sm:p-3 bg-blue-50 rounded-lg"
              >
                <div class="flex items-center space-x-2">
                  <div class="p-1 sm:p-2 rounded-full bg-blue-100 text-blue-600">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4 sm:h-5 sm:w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                  </div>
                  <span class="text-xs sm:text-sm font-medium">Shipping Cost </span>
                </div>
                <span class="text-sm sm:text-lg font-semibold text-blue-700">{{
                  formatCurrency(stock.shipping_cost)
                }}</span>
              </div>

              <div
                class="flex items-center justify-between p-2 sm:p-3 bg-purple-50 rounded-lg"
              >
                <div class="flex items-center space-x-2">
                  <div class="p-1 sm:p-2 rounded-full bg-purple-100 text-purple-600">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4 sm:h-5 sm:w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"
                      />
                    </svg>
                  </div>
                  <span class="text-xs sm:text-sm font-medium text-purple-700"
                    >Other Cost</span
                  >
                </div>
                <span class="text-sm sm:text-lg font-semibold text-purple-700">{{
                  formatCurrency(stock.other_fees)
                }}</span>
              </div>
            </div>

            <!-- Total Cost and Date Card -->
            <div class="space-y-3">
              <div
                class="flex items-center justify-between p-2 sm:p-3 bg-green-50 rounded-lg"
              >
                <div class="flex items-center space-x-2">
                  <div class="p-1 sm:p-2 rounded-full bg-green-100 text-green-600">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4 sm:h-5 sm:w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                      />
                    </svg>
                  </div>
                  <span class="text-xs sm:text-sm font-medium text-gray-600"
                    >Total Cost</span
                  >
                </div>
                <span class="text-sm sm:text-lg font-semibold text-green-700">{{
                  formatCurrency(stock.total_cost)
                }}</span>
              </div>

              <div
                class="flex items-center justify-between p-2 sm:p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center space-x-2">
                  <div class="p-1 sm:p-2 rounded-full bg-gray-100 text-gray-600">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-4 w-4 sm:h-5 sm:w-5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                      />
                    </svg>
                  </div>
                  <span class="text-xs sm:text-sm font-medium text-gray-600"
                    >Created at</span
                  >
                </div>
                <span class="text-xs sm:text-sm font-medium text-gray-700">{{
                  formatDate(stock.created_at)
                }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 mb-4">
        <nav class="flex space-x-6" aria-label="Tabs">
          <a
            href="#"
            class="border-b-2 border-blue-500 text-blue-600 py-2 px-1 text-sm font-medium"
            >Inventory</a
          >
        </nav>
      </div>

      <!-- Controls -->
      <div class="flex justify-between items-center mb-4">
        <input
          type="text"
          placeholder="Search name or prescription ID..."
          class="border px-3 py-2 rounded-md w-1/3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <div class="flex gap-2">
          <button
            class="border border-gray-300 px-4 py-2 rounded-md text-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Filters
          </button>
        </div>
      </div>

      <!-- Product Table -->
      <div class="overflow-x-auto border rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Name
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Unit Cost
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Shipping
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Other
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Landed Cost
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Total Cost
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Sale Price
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Stock
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Sold
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Price Meta
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Adjustment Meta
              </th>
              <th
                scope="col"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Action
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="item in stock.store_stock_items"
              :key="item.id"
              class="hover:bg-gray-50"
            >
              <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex items-center space-x-3">
                  <div
                    v-if="item.store_product?.primary_image_url"
                    class="w-10 h-10 shrink-0"
                  >
                    <img
                      :src="item.store_product.primary_image_url"
                      :alt="item.store_product.name"
                      class="w-10 h-10 object-cover rounded"
                    />
                  </div>
                  <div class="truncate max-w-[180px]">
                    {{ item.store_product?.name || "Unnamed Product" }}
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ formatCurrency(item.unit_cost) ?? "N/A" }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ formatCurrency(item.shipping_cost_unit) ?? "N/A" }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ formatCurrency(item.other_fees_unit) ?? "N/A" }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ formatCurrency(item.unit_cost * item.quantity) ?? "N/A" }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ formatCurrency(item.total_cost) ?? "N/A" }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ formatCurrency(item.sale_price) ?? "N/A" }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">{{ item.quantity ?? 0 }}</td>
              <td class="px-4 py-3 whitespace-nowrap">{{ item.quantity_sold ?? 0 }}</td>
              <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600">
                {{ item.price_meta || "No changes" }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-600">
                {{ item.adjustment_meta || "No changes" }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap space-x-2">
                <button
                  @click="openPriceModal(item)"
                  class="text-gray-600 hover:text-gray-800 p-1 rounded hover:bg-gray-100"
                  title="Adjustment"
                >
                  <svg
                    class="h-5 w-5 text-blue-600"
                    viewBox="0 0 32 32"
                    id="Layer_1"
                    version="1.1"
                    xml:space="preserve"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink"
                  >
                    <g>
                      <g>
                        <path
                          style="fill: #00bbb4"
                          class="st0"
                          d="M5,14.015625V30.5h12.0424805v-7.9232178c0-1.8409424,1.4923706-3.333313,3.333313-3.333313    s3.333374,1.4923706,3.333374,3.333313V30.5H27V14.015625H5z M13.553833,20.75h-4c-0.5523071,0-1-0.4477539-1-1    c0-0.5523071,0.4476929-1,1-1h4c0.5523071,0,1,0.4476929,1,1C14.553833,20.3022461,14.1061401,20.75,13.553833,20.75z"
                        />
                      </g>

                      <g>
                        <path
                          style="fill: #1b75bc"
                          class="st1"
                          d="M31,31.5H1c-0.5522461,0-1-0.4477539-1-1s0.4477539-1,1-1h30c0.5522461,0,1,0.4477539,1,1    S31.5522461,31.5,31,31.5z"
                        />
                      </g>

                      <g>
                        <path
                          style="fill: #f15a29"
                          class="st2"
                          d="M5.2246366,12.531251h-0.00001c-3.0098128,0-5.0521717-3.0604792-3.8971148-5.8398342l2.5730567-6.1914167    h6.6666656L9.4049702,8.8900909C9.1158504,10.9771776,7.3316536,12.531251,5.2246366,12.531251z"
                        />
                      </g>

                      <g>
                        <path
                          style="fill: #f15a29"
                          class="st2"
                          d="M16.1548214,12.531251h-0.3096428c-2.5610209,0-4.5317516-2.2625523-4.1803341-4.7993479l1.0018225-7.2319031    h6.666667l1.0018215,7.2319031C20.686573,10.2686987,18.7158413,12.531251,16.1548214,12.531251z"
                        />
                      </g>

                      <g>
                        <path
                          style="fill: #f15a29"
                          class="st2"
                          d="M26.7753735,12.531251h-0.0000095c-2.1070175,0-3.8912144-1.5540733-4.1803341-3.64116L21.432766,0.4999999    h6.666666l2.5730553,6.1914167C31.8275452,9.4707718,29.7851868,12.531251,26.7753735,12.531251z"
                        />
                      </g>
                    </g>
                  </svg>
                </button>
                <button
                  @click="openAdjustmentModal(item)"
                  class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50"
                  title="Update Price"
                >
                  <svg
                    height="20px"
                    width="20px"
                    version="1.1"
                    id="Capa_1"
                    xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink"
                    viewBox="0 0 449.179 449.179"
                    xml:space="preserve"
                  >
                    <g>
                      <path
                        style="fill: #4479fa"
                        d="M375.958,415.456v-80.442c0-7.185-5.529-13.131-12.694-13.67
    c-77.293-5.82-140.109-64.335-152.546-139.623c-0.488-2.956,0.349-5.976,2.286-8.262s4.783-3.614,7.778-3.614h15.999l0.001,0
    h34.756L163.176,22.199c-1.388,0.895-2.625,2.038-3.628,3.405l-101.6,138.431c-0.814,1.109-0.935,2.581-0.314,3.809
    s1.88,2.001,3.256,2.001h46.829c5.289,0,9.682,4.031,10.149,9.3c5.694,64.144,33.408,123.698,79.493,169.783
    c46,46,105.42,73.695,169.429,79.461c1.801-0.636,3.471-1.64,4.885-2.982C374.403,422.816,375.958,419.218,375.958,415.456z"
                      />
                      <path
                        style="fill: #2c3d8e"
                        d="M170.611,20c4.37,0,8.478,2.081,11.064,5.604l101.6,138.431c0.814,1.109,0.935,2.581,0.314,3.809
    s-1.88,2.001-3.256,2.001h-43.55c-2.996,0-5.842,1.329-7.778,3.614s-2.774,5.306-2.286,8.262
    c12.437,75.288,75.252,133.804,152.546,139.623c7.165,0.539,12.694,6.485,12.694,13.67v80.443c0,3.762-1.555,7.36-4.283,9.95
    c-2.558,2.428-5.943,3.774-9.452,3.774c-0.234,0-0.469-0.006-0.704-0.018c-68.084-3.495-131.598-31.674-180.159-80.235
    c-46.085-46.085-73.798-105.638-79.493-169.783c-0.468-5.268-4.86-9.3-10.149-9.3H60.89c-1.376,0-2.634-0.773-3.256-2.001
    s-0.5-2.7,0.314-3.809l101.6-138.431C162.133,22.081,166.241,20,170.611,20 M170.611,0c-10.696,0-20.859,5.148-27.187,13.77
    l-101.6,138.431c-5.304,7.227-6.082,16.683-2.032,24.68c4.049,7.995,12.133,12.963,21.097,12.963h37.984
    c3.707,31.121,12.261,61.164,25.475,89.433c14.517,31.054,34.323,59.246,58.869,83.792c25.884,25.884,55.75,46.457,88.77,61.148
    c32.966,14.667,68.127,23.051,104.505,24.919c0.571,0.029,1.154,0.044,1.729,0.044c8.677,0,16.924-3.291,23.22-9.268
    c6.682-6.343,10.515-15.257,10.515-24.456v-80.443c0-8.541-3.199-16.687-9.007-22.94c-5.799-6.243-13.678-10.034-22.185-10.674
    c-64.079-4.825-116.837-50.033-131.978-111.556h31.545c8.963,0,17.048-4.967,21.098-12.964c4.05-7.995,3.272-17.452-2.031-24.678
    L197.798,13.77C191.47,5.148,181.306,0,170.611,0L170.611,0z"
                      />
                    </g>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Price Update Modal -->
      <div
        v-if="showPriceModal"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
      >
        <!-- Glass overlay -->
        <div
          class="fixed inset-0 bg-black/30 backdrop-blur-sm"
          @click="showPriceModal = false"
        ></div>

        <!-- Modal content -->
        <div
          class="relative bg-white rounded-lg p-6 w-full max-w-md shadow-xl border border-gray-200 overflow-y-auto max-h-[90vh]"
        >
          <h2 class="text-xl font-semibold mb-4 text-gray-900 flex items-center gap-2">
            <svg
              class="h-5 w-5 text-blue-600 shrink-0"
              viewBox="0 0 32 32"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M5 14.0156V30.5H17.0425V22.5768C17.0425 20.7358 18.5348 19.2435 20.3758 19.2435C22.2167 19.2435 23.709 20.7358 23.709 22.5768V30.5H27V14.0156H5Z"
                fill="#00BBB4"
              />
              <path
                d="M13.5538 20.75H9.55383C9.00152 20.75 8.55383 20.3022 8.55383 19.75C8.55383 19.1977 9.00152 18.75 9.55383 18.75H13.5538C14.1061 18.75 14.5538 19.1977 14.5538 19.75C14.5538 20.3022 14.1061 20.75 13.5538 20.75Z"
                fill="white"
              />
              <path
                d="M31 31.5H1C0.447754 31.5 0 31.0522 0 30.5C0 29.9478 0.447754 29.5 1 29.5H31C31.5522 29.5 32 29.9478 32 30.5C32 31.0522 31.5522 31.5 31 31.5Z"
                fill="#1B75BC"
              />
              <path
                d="M5.22464 12.5312C2.21482 12.5312 0.172466 9.47077 1.32752 6.69142L3.90058 0.5H10.5672L9.40497 8.89009C9.11585 10.9772 7.33165 12.5312 5.22464 12.5312Z"
                fill="#F15A29"
              />
              <path
                d="M16.1548 12.5312H15.8452C13.2842 12.5312 11.3134 10.2687 11.6649 7.7319L12.6667 0.5H19.3333L20.3351 7.7319C20.6866 10.2687 18.7158 12.5312 16.1548 12.5312Z"
                fill="#F15A29"
              />
              <path
                d="M26.7754 12.5312C24.6684 12.5312 22.8842 10.9772 22.595 8.89009L21.4328 0.5H28.0994L30.6725 6.69142C31.8275 9.47077 29.7852 12.5312 26.7754 12.5312Z"
                fill="#F15A29"
              />
            </svg>
            <span
              >Update Price for {{ selectedItem?.store_product?.name || "Product" }}</span
            >
          </h2>

          <div class="space-y-4">
            <div>
              <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1"
                >Sale Price *</label
              >
              <input
                type="number"
                id="sale_price"
                v-model="priceForm.sale_price"
                placeholder="Enter new price"
                required
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                step="0.01"
                min="0"
              />
            </div>

            <div>
              <label for="price_note" class="block text-sm font-medium text-gray-700 mb-1"
                >Note (Optional)</label
              >
              <textarea
                id="price_note"
                v-model="priceForm.note"
                rows="3"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                placeholder="Reason for price change..."
              ></textarea>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 mt-6">
            <button
              @click="showPriceModal = false"
              class="px-4 py-2 rounded-md border text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              @click="submitPriceUpdate"
              class="px-5 py-2 rounded-md text-sm font-medium shadow-sm bg-gray-800 text-white hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Update Price
            </button>
          </div>
        </div>
      </div>

      <!-- Adjustment Modal -->
      <div
        v-if="showAdjustmentModal"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
      >
        <!-- Glass overlay -->
        <div
          class="fixed inset-0 bg-black/30 backdrop-blur-sm"
          @click="showAdjustmentModal = false"
        ></div>

        <!-- Modal content -->
        <div
          class="relative bg-white rounded-lg p-6 w-full max-w-md shadow-xl border border-gray-200 overflow-y-auto max-h-[90vh]"
        >
          <h2 class="text-xl font-semibold mb-4 text-gray-900 flex items-center gap-2">
            <svg
              class="h-5 w-5 text-blue-600 shrink-0"
              viewBox="0 0 32 32"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M5 14.0156V30.5H17.0425V22.5768C17.0425 20.7358 18.5348 19.2435 20.3758 19.2435C22.2167 19.2435 23.709 20.7358 23.709 22.5768V30.5H27V14.0156H5Z"
                fill="#00BBB4"
              />
              <path
                d="M13.5538 20.75H9.55383C9.00152 20.75 8.55383 20.3022 8.55383 19.75C8.55383 19.1977 9.00152 18.75 9.55383 18.75H13.5538C14.1061 18.75 14.5538 19.1977 14.5538 19.75C14.5538 20.3022 14.1061 20.75 13.5538 20.75Z"
                fill="white"
              />
              <path
                d="M31 31.5H1C0.447754 31.5 0 31.0522 0 30.5C0 29.9478 0.447754 29.5 1 29.5H31C31.5522 29.5 32 29.9478 32 30.5C32 31.0522 31.5522 31.5 31 31.5Z"
                fill="#1B75BC"
              />
              <path
                d="M5.22464 12.5312C2.21482 12.5312 0.172466 9.47077 1.32752 6.69142L3.90058 0.5H10.5672L9.40497 8.89009C9.11585 10.9772 7.33165 12.5312 5.22464 12.5312Z"
                fill="#F15A29"
              />
              <path
                d="M16.1548 12.5312H15.8452C13.2842 12.5312 11.3134 10.2687 11.6649 7.7319L12.6667 0.5H19.3333L20.3351 7.7319C20.6866 10.2687 18.7158 12.5312 16.1548 12.5312Z"
                fill="#F15A29"
              />
              <path
                d="M26.7754 12.5312C24.6684 12.5312 22.8842 10.9772 22.595 8.89009L21.4328 0.5H28.0994L30.6725 6.69142C31.8275 9.47077 29.7852 12.5312 26.7754 12.5312Z"
                fill="#F15A29"
              />
            </svg>
            <span
              >Stock Adjustment for
              {{ selectedItem?.store_product?.name || "Product" }}</span
            >
          </h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Adjustment Type *</label
              >
              <div class="mt-1 flex gap-6">
                <div class="flex items-center">
                  <input
                    id="damage"
                    v-model="adjustmentForm.type"
                    type="radio"
                    value="damage"
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                  />
                  <label for="damage" class="ml-2 block text-sm text-gray-700"
                    >Damage</label
                  >
                </div>
                <div class="flex items-center">
                  <input
                    id="return"
                    v-model="adjustmentForm.type"
                    type="radio"
                    value="return"
                    class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                  />
                  <label for="return" class="ml-2 block text-sm text-gray-700"
                    >Return</label
                  >
                </div>
              </div>
            </div>

            <div>
              <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1"
                >Quantity *</label
              >
              <input
                type="number"
                id="quantity"
                v-model="adjustmentForm.quantity"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                min="1"
                :max="selectedItem?.quantity || 1"
                required
              />
              <p class="mt-1 text-xs text-gray-500">
                Current stock: {{ selectedItem?.quantity }}
              </p>
            </div>

            <div>
              <label
                for="adjustment_note"
                class="block text-sm font-medium text-gray-700 mb-1"
                >Note *</label
              >
              <textarea
                id="adjustment_note"
                v-model="adjustmentForm.note"
                rows="3"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                placeholder="Details about the adjustment..."
                required
              ></textarea>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 mt-6">
            <button
              @click="showAdjustmentModal = false"
              class="px-4 py-2 rounded-md border text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              @click="submitAdjustment"
              :disabled="!adjustmentForm.note"
              :class="{
                'bg-gray-800 hover:bg-gray-900': adjustmentForm.note,
                'bg-gray-400 cursor-not-allowed': !adjustmentForm.note,
              }"
              class="px-5 py-2 rounded-md text-sm font-medium shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Submit Adjustment
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
