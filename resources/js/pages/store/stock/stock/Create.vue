<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted } from "vue";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

// Props
const props = defineProps({
  products: {
    type: Array,
    required: true,
  },
  invoice_number: {
    type: String,
    required: true,
  },
});

// Breadcrumbs
const breadcrumbs = [
  { title: "Stocks", href: "/store/stocks" },
  { title: "Add Stock", href: "/store/stocks/create" },
];

// Form
const form = useForm({
  invoice_number: props.invoice_number,
  supplier_name: "",
  shipping_cost: 0,
  other_fees: 0,
  total_cost: 0,
  products: [],
  document: null,
  note: "",
});

// Refs
const searchQuery = ref("");
const showProductDropdown = ref(false);
const imagePreview = ref(null);
const editor = ClassicEditor;

// Computed
const filteredProducts = computed(() =>
  !searchQuery.value
    ? []
    : props.products.filter(
        (product) =>
          product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) &&
          !form.products.some((p) => p.id === product.id)
      )
);

const subtotal = computed(() =>
  form.products.reduce((sum, p) => sum + p.quantity * p.unit_cost, 0)
);

const totalQuantity = computed(() =>
  form.products.reduce((sum, p) => sum + p.quantity, 0)
);

const grandTotal = computed(
  () =>
    subtotal.value +
    parseFloat(form.shipping_cost || 0) +
    parseFloat(form.other_fees || 0)
);

// Watchers
watch([subtotal, () => form.shipping_cost, () => form.other_fees, totalQuantity], () => {
  form.total_cost = grandTotal.value;
  distributeAdditionalCosts();
});

// Methods
function distributeAdditionalCosts() {
  const shipping = parseFloat(form.shipping_cost || 0);
  const other = parseFloat(form.other_fees || 0);
  const totalQty = totalQuantity.value;

  form.products.forEach((product) => {
    // Calculate per-unit allocation
    const shippingPerUnit = totalQty > 0 ? shipping / totalQty : 0;
    const otherFeesPerUnit = totalQty > 0 ? other / totalQty : 0;

    // Update product costs (per unit basis)
    product.shipping_cost_per_unit = shippingPerUnit;
    product.other_fees_per_unit = otherFeesPerUnit;

    // Calculate total costs for the product
    product.shipping_cost = shippingPerUnit * product.quantity;
    product.other_fees = otherFeesPerUnit * product.quantity;

    // Calculate unit landed cost (base cost + allocated fees)
    product.unit_landed_cost = product.unit_cost + shippingPerUnit + otherFeesPerUnit;

    // Calculate total product cost
    product.total_cost = product.quantity * product.unit_landed_cost;
  });
}

function addProduct(product) {
  form.products.push({
    id: product.id,
    name: product.name,
    quantity: 1,
    unit_cost: product.cost_price || 0,
    shipping_cost_per_unit: 0,
    other_fees_per_unit: 0,
    unit_landed_cost: product.cost_price || 0,
    shipping_cost: 0,
    other_fees: 0,
    total_cost: product.cost_price || 0,
    sale_price: product.sale_price || 0,
  });

  searchQuery.value = "";
  showProductDropdown.value = false;
  distributeAdditionalCosts();
}

function removeProduct(index) {
  form.products.splice(index, 1);
  distributeAdditionalCosts();
}

function updateProductTotal(index) {
  const product = form.products[index];
  // Recalculate based on new quantity
  product.shipping_cost = product.shipping_cost_per_unit * product.quantity;
  product.other_fees = product.other_fees_per_unit * product.quantity;
  product.total_cost = product.quantity * product.unit_landed_cost;
  distributeAdditionalCosts();
}

function handleSearchFocus() {
  if (searchQuery.value) showProductDropdown.value = true;
}

function handleImageUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  form.document = file;

  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
}

function removeImage() {
  form.document = null;
  imagePreview.value = null;

  const fileInput = document.querySelector('input[type="file"]');
  if (fileInput) fileInput.value = "";
}

function submitForm() {
  router.post("/store/stocks", form, {
    onSuccess: () => alert('Stock added successfully!'),
    preserveScroll: true
  });
}
</script>

<template>
  <Head title="Add Stock" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-100">
      <!-- Header Section -->
      <div class="flex items-center justify-between mb-6 pb-4 border-b">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Add New Stock</h1>
          <p class="text-gray-600 mt-1">
            Fill in the details of your new stock inventory
          </p>
        </div>
        <img
          src="https://cdn-icons-png.flaticon.com/512/3058/3058979.png"
          alt="Inventory cartoon"
          class="w-20 h-20 animate-bounce"
          style="animation-duration: 3s"
        />
      </div>

      <!-- Form Sections -->
      <div class="space-y-8">
        <!-- Basic Information Section -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-1 h-6 bg-blue-500 mr-3 rounded-full"></div>
            <h2 class="text-lg font-semibold text-gray-800">Basic Information</h2>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Invoice Number*</label
              >
              <input
                v-model="form.invoice_number"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-gray-50 text-gray-500 filter blur-[0.4px]"
                readonly
                disabled
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Supplier Name</label
              >
              <input
                v-model="form.supplier_name"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                placeholder="Supplier company name"
              />
              <p v-if="form.errors.supplier_name" class="mt-1 text-sm text-red-600">
                {{ form.errors.supplier_name }}
              </p>
            </div>
          </div>
        </div>

        <!-- Products Section -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
              <div class="w-1 h-6 bg-blue-500 mr-3 rounded-full"></div>
              <h2 class="text-lg font-semibold text-gray-800">Products</h2>
            </div>
            <span class="text-sm text-gray-500">{{ form.products.length }} items</span>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Add Products</label
            >
            <div class="relative">
              <input
                v-model="searchQuery"
                @focus="handleSearchFocus"
                @input="showProductDropdown = searchQuery.length > 0"
                type="text"
                placeholder="Search products by name..."
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              />
              <ul
                v-if="showProductDropdown && filteredProducts.length"
                class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto"
              >
                <li
                  v-for="product in filteredProducts"
                  :key="product.id"
                  @mousedown.prevent="addProduct(product)"
                  class="px-4 py-2 hover:bg-gray-50 cursor-pointer text-sm flex items-center border-b border-gray-100 last:border-0"
                >
                  <span class="text-gray-400 mr-2">•</span>
                  {{ product.name }}
                  <span class="ml-auto text-gray-500"
                    >${{ product.cost_price?.toFixed(2) || "0.00" }}</span
                  >
                </li>
              </ul>
            </div>
            <p v-if="form.errors.products" class="mt-1 text-sm text-red-600">
              {{ form.errors.products }}
            </p>
          </div>

          <!-- Product Table -->
          <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Product
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Quantity
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Unit Cost
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Shipping/Unit
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Fees/Unit
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Landed Cost
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Total
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Sale Price
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Action
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="(item, index) in form.products"
                  :key="item.id"
                  class="hover:bg-gray-50"
                >
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                  >
                    {{ item.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      v-model.number="item.quantity"
                      @change="updateProductTotal(index)"
                      type="number"
                      min="1"
                      class="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      v-model.number="item.unit_cost"
                      @change="distributeAdditionalCosts"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${{ item.shipping_cost_per_unit.toFixed(2) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${{ item.other_fees_per_unit.toFixed(2) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                    ${{ item.unit_landed_cost.toFixed(2) }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium"
                  >
                    ${{ item.total_cost.toFixed(2) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      v-model.number="item.sale_price"
                      type="number"
                      min="0"
                      step="0.01"
                      class="w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <button
                      @click="removeProduct(index)"
                      class="text-red-600 hover:text-red-800"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div
            v-if="form.products.length === 0"
            class="text-center py-8 border border-gray-200 rounded-lg mt-4"
          >
            <svg
              class="mx-auto h-12 w-12 text-gray-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
              />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No products added</h3>
            <p class="mt-1 text-sm text-gray-500">
              Search and select products above to add them to this stock entry.
            </p>
          </div>
        </div>

        <!-- Costs Section -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-1 h-6 bg-blue-500 mr-3 rounded-full"></div>
            <h2 class="text-lg font-semibold text-gray-800">Costs & Summary</h2>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Subtotal</label>
              <div
                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-md text-right font-medium text-gray-900"
              >
                ${{ subtotal.toFixed(2) }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Shipping Cost</label
              >
              <input
                v-model.number="form.shipping_cost"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                placeholder="0.00"
              />
              <p v-if="form.errors.shipping_cost" class="mt-1 text-sm text-red-600">
                {{ form.errors.shipping_cost }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Other Fees</label
              >
              <input
                v-model.number="form.other_fees"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                placeholder="0.00"
              />
              <p v-if="form.errors.other_fees" class="mt-1 text-sm text-red-600">
                {{ form.errors.other_fees }}
              </p>
            </div>
          </div>

          <!-- Grand Total Section -->
          <div class="flex justify-end">
            <div class="w-full md:w-1/2 lg:w-1/3">
              <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                <div class="space-y-3">
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Subtotal:</span>
                    <span class="text-sm font-medium">${{ subtotal.toFixed(2) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Shipping:</span>
                    <span class="text-sm font-medium"
                      >${{ parseFloat(form.shipping_cost || 0).toFixed(2) }}</span
                    >
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Other Fees:</span>
                    <span class="text-sm font-medium"
                      >${{ parseFloat(form.other_fees || 0).toFixed(2) }}</span
                    >
                  </div>
                  <div class="border-t border-gray-200 pt-3 mt-2">
                    <div class="flex justify-between">
                      <span class="text-base font-semibold">Grand Total:</span>
                      <span class="text-base font-bold text-blue-600"
                        >${{ grandTotal.toFixed(2) }}</span
                      >
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes and Document Section -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
          <div class="flex items-center mb-6">
            <div class="w-1 h-6 bg-blue-500 mr-3 rounded-full"></div>
            <h2 class="text-lg font-semibold text-gray-800">Additional Information</h2>
          </div>
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <ckeditor
              :editor="editor"
              v-model="form.note"
              :config="{
                toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList'],
              }"
              class="rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500"
            />
            <p v-if="form.errors.note" class="mt-1 text-sm text-red-600">
              {{ form.errors.note }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Attach Document</label
            >
            <div
              class="rounded-lg border-2 border-dashed border-gray-300 p-6 text-center"
            >
              <div v-if="imagePreview" class="space-y-4">
                <img
                  :src="imagePreview"
                  alt="Document preview"
                  class="w-full h-48 object-contain mx-auto rounded-md"
                />
                <button
                  @click="removeImage"
                  type="button"
                  class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Remove Document
                </button>
              </div>
              <div v-else class="space-y-3">
                <svg
                  class="mx-auto h-12 w-12 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                  />
                </svg>
                <p class="text-sm text-gray-600">
                  Upload supporting documents (PDF, JPG, PNG)
                </p>
                <label class="cursor-pointer">
                  <span class="sr-only">Choose document</span>
                  <input
                    type="file"
                    @change="handleImageUpload"
                    accept="image/*,.pdf"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                  />
                </label>
              </div>
            </div>
            <p v-if="form.errors.document" class="mt-1 text-sm text-red-600">
              {{ form.errors.document }}
            </p>
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end pt-6 animate-on-load">
        <button
          @click="submitForm"
          :disabled="form.processing || form.products.length === 0"
          class="px-6 py-2 text-white rounded-md transition disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none"
          style="
            background-color: #0984e3;
            box-shadow: 0 4px 0 #0767b1;
            transform: translateY(0);
          "
          :class="{
            'hover:bg-blue-700': !form.processing,
            'active:transform active:translateY(1px) active:shadow-none': !form.processing,
          }"
        >
          <span v-if="form.processing">Processing...</span>
          <span v-else class="flex items-center">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 mr-1"
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
            Submit Stock
          </span>
        </button>
      </div>
    </div>
  </AppLayout>
</template>
