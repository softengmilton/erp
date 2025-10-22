<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, computed, watch, onMounted } from "vue";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
import Card from "@/components/ui/card/Card.vue";
import CardTitle from "@/components/ui/card/CardTitle.vue";
import CardHeader from "@/components/ui/card/CardHeader.vue";
import CardContent from "@/components/ui/card/CardContent.vue";
import CardFooter from "@/components/ui/card/CardFooter.vue";
import Label from "@/components/ui/label/Label.vue";
import Input from "@/components/ui/input/Input.vue";
import InputError from "@/components/InputError.vue";
import StoreSetting, { initStoreSetting } from "@/utils/module/StoreSetting";

// Initialize store settings
initStoreSetting();


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
const isFormValid = computed(() => {
  const hasInvoice = !!form.invoice_number;
  const hasSupplier = !!form.supplier_name.trim();
  const hasProducts = form.products.length > 0;

  const validProducts = form.products.every(
    (p) => p.quantity > 0 && p.unit_cost > 0 && p.sale_price>0
  );

  return hasInvoice && hasSupplier && hasProducts && validProducts;
});

const breadcrumbs = [
  { title: "Stocks", href: "/store/stocks" },
  { title: "Add Stock", href: "/store/stocks/create" },
];

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

const searchQuery = ref("");
const showProductDropdown = ref(false);
const imagePreview = ref(null);
const editor = ClassicEditor;

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
    quantity: 0,
    unit_cost: 0,
    shipping_cost_per_unit: 0,
    other_fees_per_unit: 0,
    unit_landed_cost: 0,
    shipping_cost: 0,
    other_fees: 0,
    total_cost: 0,
    sale_price: 0,
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
function printInvoice() {
  const printWindow = window.open("", "", "width=900,height=650");
  const today = new Date().toLocaleDateString();

const settings = StoreSetting.all.value;
  const formatCurrency = (val) => Number(val || 0).toFixed(2);
  const formatNumber = (val) => Number(val || 0);

  const productsRows = (form.products || [])
    .map(
      (p) => `
        <tr>
          <td style="border:1px solid #ccc;padding:6px;">${p.name ?? ""}</td>
          <td style="border:1px solid #ccc;padding:6px;text-align:center;">${formatNumber(p.quantity)}</td>
          <td style="border:1px solid #ccc;padding:6px;text-align:right;">$${formatCurrency(p.unit_cost)}</td>
          <td style="border:1px solid #ccc;padding:6px;text-align:right;">$${formatCurrency(p.unit_landed_cost)}</td>
          <td style="border:1px solid #ccc;padding:6px;text-align:right;">$${formatCurrency(p.total_cost)}</td>
        </tr>
      `
    )
    .join("");

  printWindow.document.write(`
    <html>
      <head>
        <title>Purchase Invoice - ${form.invoice_number ?? ""}</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          h1, h2, h3 { margin: 0 0 10px; }
          table { border-collapse: collapse; width: 100%; margin-top: 20px; font-size: 14px; }
          th { background: #f4f4f4; border:1px solid #ccc; padding:6px; }
          .header{text-align:center}
        </style>
      </head>
      <body>
            <div class="header">
            <h1>${settings.business_title || "Store Name"}</h1>
            <p>${settings.address || ""}</p>
            <p>${settings.business_email || ""} | ${settings.phone || ""}</p>
              <h1>Purchase Invoice</h1>
        <p><strong>Date:</strong> ${today}</p>
        <p><strong>Invoice #:</strong> ${form.invoice_number ?? "-"}</p>
        <p><strong>Supplier:</strong> ${form.supplier_name ?? "-"}</p>
          </div>


        <table>
          <thead>
            <tr>
              <th>Product</th>
              <th>Qty</th>
              <th>Unit Cost</th>
              <th>Landed Cost</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            ${productsRows}
          </tbody>
        </table>

        <h3 style="margin-top:20px;">Summary</h3>
        <p><strong>Subtotal:</strong> $${formatCurrency(subtotal.value)}</p>
        <p><strong>Shipping:</strong> $${formatCurrency(form.shipping_cost)}</p>
        <p><strong>Other Fees:</strong> $${formatCurrency(form.other_fees)}</p>
        <p><strong>Grand Total:</strong> $${formatCurrency(grandTotal.value)}</p>

        <p style="margin-top:20px;"><strong>Notes:</strong><br/>${form.note ?? "-"}</p>
               <div class="footer">
            <p>Thank you for your business!</p>
            <p style="margin-top:8px; font-size:11px; color:#999;">
                Powered by <a href="https://infinityflamesoft.com/" target="_blank" style="color:#0d9488; text-decoration:none;">Infinity Flame Soft</a>
            </p>
        </div>
      </body>
    </html>
  `);

  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
}

function submitForm() {
  router.post("/store/stocks", form, {
    onSuccess: () => {
      searchQuery.value = "";
      showProductDropdown.value = false;
      printInvoice();
    },
    preserveScroll: true,
  });
}
</script>

<template>
  <Head title="Add Stock" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-8 rounded-lg shadow-sm dark:border dark:border-gray-700">
      <!-- Header Section -->
      <div class="flex items-center justify-between mb-6 pb-4 border-b">
        <div>
          <h1 class="text-3xl font-bold">Add New Stock</h1>
          <p class="mt-1">Fill in the details of your new stock inventory</p>
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
        <Card>
          <CardHeader class="flex items-center">
            <div class="w-1 h-6 bg-blue-500 mr-3 rounded-full"></div>
            <CardTitle class="text-lg font-semibold">Basic Information</CardTitle>
          </CardHeader>
          <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Invoice Number -->
            <div>
              <Label for="invoice_number">Invoice Number*</Label>
              <Input
                id="invoice_number"
                v-model="form.invoice_number"
                type="text"
                readonly
                disabled
              />
              <InputError :message="form.errors.invoice_number" />
            </div>

            <!-- Supplier Name -->
            <div>
              <Label for="supplier_name">Supplier Name</Label>
              <Input
                id="supplier_name"
                v-model="form.supplier_name"
                placeholder="Supplier company name"
              />
              <InputError :message="form.errors.supplier_name" />
            </div>
          </CardContent>
        </Card>

        <!-- Products Section -->
        <Card>
          <CardHeader class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-1 h-6 bg-blue-500 mr-3 rounded-full"></div>
              <CardTitle class="text-lg font-semibold">Products</CardTitle>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400"
              >{{ form.products.length }} items</span
            >
          </CardHeader>
          <CardContent>
            <div class="mb-6">
              <Label>Add Products</Label>
              <div class="relative">
                <Input
                  v-model="searchQuery"
                  @focus="handleSearchFocus"
                  @input="showProductDropdown = searchQuery.length > 0"
                  type="text"
                  placeholder="Search products by name..."
                />
                <ul
                  v-if="showProductDropdown && filteredProducts.length"
                  class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-y-auto"
                >
                  <li
                    v-for="product in filteredProducts"
                    :key="product.id"
                    @mousedown.prevent="addProduct(product)"
                    class="px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-600 cursor-pointer text-sm flex items-center border-b border-gray-100 dark:border-gray-600 last:border-0"
                  >
                    <span class="text-gray-400 mr-2">•</span>
                    <span class="dark:text-gray-200">{{ product.name }}</span>
                    <span class="ml-auto text-gray-500 dark:text-gray-400"
                      >${{ product.cost_price?.toFixed(2) || "0.00" }}</span
                    >
                  </li>
                </ul>
              </div>
              <InputError :message="form.errors.products" />
            </div>

            <!-- Product Table -->
            <div class="overflow-x-auto border rounded-lg">
              <table class="min-w-full divide-y">
                <thead>
                  <tr>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Product
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Quantity
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Unit Cost
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Shipping/Unit
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Fees/Unit
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Landed Cost
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Total
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Sale Price
                    </th>
                    <th
                      scope="col"
                      class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                    >
                      Action
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y">
                  <tr v-for="(item, index) in form.products" :key="item.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      {{ item.name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <Input
                        v-model.number="item.quantity"
                        @change="updateProductTotal(index)"
                        type="number"
                        min="1"
                        class="w-20"
                      />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <Input
                        v-model.number="item.unit_cost"
                        @change="distributeAdditionalCosts"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-24"
                      />
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      ${{ item.shipping_cost_per_unit.toFixed(2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                      ${{ item.other_fees_per_unit.toFixed(2) }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400"
                    >
                      ${{ item.unit_landed_cost.toFixed(2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      ${{ item.total_cost.toFixed(2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <Input
                        v-model.number="item.sale_price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-24"
                      />
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                    >
                      <button
                        @click="removeProduct(index)"
                        class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-600"
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
              class="text-center py-8 border rounded-lg mt-4"
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
              <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-200">
                No products added
              </h3>
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Search and select products above to add them to this stock entry.
              </p>
            </div>
          </CardContent>
        </Card>

        <!-- Costs Section -->
        <Card>
          <CardHeader class="flex items-center">
            <div class="w-1 h-6 bg-blue-500 mr-3 rounded-full"></div>
            <CardTitle class="text-lg font-semibold">Costs & Summary</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
              <div>
                <Label>Subtotal</Label>
                <div
                  class="w-full px-4 py-3 border rounded-md text-right font-medium blur[0.4]"
                >
                  ${{ subtotal.toFixed(2) }}
                </div>
              </div>
              <div>
                <Label for="shipping_cost">Shipping Cost</Label>
                <Input
                  id="shipping_cost"
                  v-model.number="form.shipping_cost"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                />
                <InputError :message="form.errors.shipping_cost" />
              </div>
              <div>
                <Label for="other_fees">Other Fees</Label>
                <Input
                  id="other_fees"
                  v-model.number="form.other_fees"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="0.00"
                />
                <InputError :message="form.errors.other_fees" />
              </div>
            </div>

            <!-- Grand Total Section -->
            <div class="flex justify-end">
              <div class="w-full md:w-1/2 lg:w-1/3">
                <div class="p-6 rounded-lg border">
                  <div class="space-y-3">
                    <div class="flex justify-between">
                      <span class="text-sm">Subtotal:</span>
                      <span class="text-sm font-medium">${{ subtotal.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm">Shipping:</span>
                      <span class="text-sm font-medium"
                        >${{ parseFloat(form.shipping_cost || 0).toFixed(2) }}</span
                      >
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm">Other Fees:</span>
                      <span class="text-sm font-medium"
                        >${{ parseFloat(form.other_fees || 0).toFixed(2) }}</span
                      >
                    </div>
                    <div class="border-t pt-3 mt-2">
                      <div class="flex justify-between">
                        <span class="text-base font-semibold">Grand Total:</span>
                        <span class="text-base font-bold text-blue-600 dark:text-blue-400"
                          >${{ grandTotal.toFixed(2) }}</span
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Notes and Document Section -->
        <Card>
          <CardHeader class="flex items-center">
            <div class="w-1 h-6 bg-blue-500 mr-3 rounded-full"></div>
            <CardTitle class="text-lg font-semibold">Additional Information</CardTitle>
          </CardHeader>
          <CardContent class="space-y-6">
            <div>
              <Label>Notes</Label>
              <ckeditor
                :editor="editor"
                v-model="form.note"
                :config="{
                  toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList'],
                }"
                class="rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-200"
              />
              <InputError :message="form.errors.note" />
            </div>

            <div>
              <Label>Attach Document</Label>
              <div
                class="rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-6 text-center"
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
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    Upload supporting documents (PDF, JPG, PNG)
                  </p>
                  <label class="cursor-pointer">
                    <span class="sr-only">Choose document</span>
                    <input
                      type="file"
                      @change="handleImageUpload"
                      accept="image/*,.pdf"
                      class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-600 dark:file:text-gray-200 dark:hover:file:bg-gray-500"
                    />
                  </label>
                </div>
              </div>
              <InputError :message="form.errors.document" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Submit Button -->
      <CardFooter class="flex justify-end pt-6 animate-on-load">
        <button
        :disabled="!isFormValid"
          @click="submitForm"
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
      </CardFooter>
    </div>
  </AppLayout>
</template>
