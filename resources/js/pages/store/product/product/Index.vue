<script setup>
import { ref, computed } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

const editor = ClassicEditor;
const props = defineProps({
  products: Object, // Laravel paginator object
  productTypes: Array, // List of product types for dropdown
});

const breadcrumbs = [
  { title: 'Products', href: '/store/products' },
];

const searchQuery = ref('');
const selectedProduct = ref(null);
const isModalOpen = ref(false);
const isEditing = ref(false);
const isDeleting = ref(false);
const imagePreview = ref(null);
const imageFile = ref(null);

const filteredProducts = computed(() => {
  if (!searchQuery.value) return props.products.data;
  return props.products.data.filter((product) =>
    product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    (product.barcode && product.barcode.toLowerCase().includes(searchQuery.value.toLowerCase()))
  );
});

function openAddModal() {
  selectedProduct.value = {
    name: '',
    description: '',
    barcode: '',
    unit: '',
    low_stock_alert: 10,
    store_product_type_id: null,
  };
  imagePreview.value = null;
  imageFile.value = null;
  isEditing.value = false;
  isModalOpen.value = true;
}

function openEditModal(product) {
  selectedProduct.value = { ...product };
  imagePreview.value = product.primary_image_url;
  imageFile.value = null;
  isEditing.value = true;
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  selectedProduct.value = null;
  imagePreview.value = null;
  imageFile.value = null;
}

function handleImageUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  imageFile.value = file;

  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
}

function removeImage() {
  imagePreview.value = null;
  imageFile.value = null;
  if (selectedProduct.value.primary_image_url) {
    selectedProduct.value.primary_image_url = null;
  }
}

async function saveProduct() {
  if (!selectedProduct.value.name.trim()) {
    alert('Name is required');
    return;
  }

  const formData = new FormData();

  // Append all product data
  for (const key in selectedProduct.value) {
    if (selectedProduct.value[key] !== null) {
      formData.append(key, selectedProduct.value[key]);
    }
  }

  // Append image file if exists
  if (imageFile.value) {
    formData.append('image', imageFile.value);
  }

  if (isEditing.value) {
    formData.append('_method', 'PUT');
    await router.post(`/store/products/${selectedProduct.value.id}`, formData, {
      onSuccess: () => closeModal(),
      forceFormData: true,
    });
  } else {
    await router.post('/store/products', formData, {
      onSuccess: () => closeModal(),
      forceFormData: true,
    });
  }
}

function openDeleteConfirm(product) {
  selectedProduct.value = { ...product };
  isDeleting.value = true;
}

function closeDeleteConfirm() {
  isDeleting.value = false;
  selectedProduct.value = null;
}

function deleteProduct() {
  if (!selectedProduct.value?.id) return;
  router.delete(`/store/products/${selectedProduct.value.id}`, {
    onSuccess: () => {
      closeDeleteConfirm();
    },
  });
}

function goToPage(url) {
  if (!url) return;
  router.get(url);
}
</script>

<template>
  <Head title="Products" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div
        class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search products..."
          class="sm:w-64 px-4 py-2 border rounded-md
                 bg-white text-gray-900 placeholder-gray-500
                 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                 transition-colors duration-200"
        />
        <button
          @click="openAddModal"
          class="inline-flex items-center justify-center px-5 py-2
                 bg-gray-700 text-white font-semibold rounded-md shadow-sm
                 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                 transition-colors duration-200"
        >
          + Add Product
        </button>
      </div>

      <div
        class="overflow-x-auto border rounded-lg
               border-gray-200 bg-white"
      >
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider
                       text-gray-500"
              >
                Product Name
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider
                       text-gray-500"
              >
                Product Type
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider
                       text-gray-500"
              >
                Bar Code
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider
                       text-gray-500"
              >
                Unit
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider
                       text-gray-500"
              >
                Stock Alert
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider
                       text-gray-500"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="product in filteredProducts"
              :key="product.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center space-x-3">
                  <img
                    :src="product.primary_image_url"
                    :alt="product.name"
                    class="w-12 h-12 rounded-md object-cover"
                    onerror="this.src='https://via.placeholder.com/48?text=No+Image'"
                  >
                  <span class="text-sm font-bold text-gray-900">
                    {{ product.name }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ product.store_product_type?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ product.barcode || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ product.unit || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ product.low_stock_alert }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                <button
                  @click="openEditModal(product)"
                  class="text-blue-600 hover:text-blue-800
                         focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 rounded"
                  aria-label="Edit"
                >
                  Edit
                </button>
                <button
                  @click="openDeleteConfirm(product)"
                  class="text-red-600 hover:text-red-800
                         focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 rounded"
                  aria-label="Delete"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="filteredProducts.length === 0">
              <td colspan="7" class="px-6 py-6 text-center text-gray-500">
                No products found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav class="mt-6 flex justify-center space-x-2" aria-label="Pagination">
        <button
          v-for="link in props.products.links"
          :key="link.label"
          :disabled="!link.url"
          @click.prevent="goToPage(link.url)"
          class="px-4 py-2 border rounded-md text-sm font-medium
                 border-gray-300 text-gray-700 hover:bg-gray-50
                 disabled:opacity-50 disabled:cursor-not-allowed
                 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1
                 transition-colors duration-200"
          :class="{
            'bg-blue-50 border-blue-500 text-blue-600': link.active
          }"
          v-html="link.label"
          aria-current="page"
        ></button>
      </nav>

      <!-- Add/Edit Modal -->
        <div
        v-if="isModalOpen"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        >
        <!-- Glass overlay -->
        <div
            class="fixed inset-0 backdrop-blur-sm"
            @click="closeModal"
        ></div>

        <!-- Modal content -->
        <div class="relative bg-white/90 backdrop-blur-md rounded-lg p-6 w-full max-w-4xl shadow-xl border border-white/20 overflow-y-auto max-h-[90vh]">
            <h2 class="text-xl font-semibold mb-4 text-gray-900">
            {{ isEditing ? 'Edit' : 'Add' }} Product
            </h2>

            <!-- First Row - Image and Name -->
            <div class="flex flex-col md:flex-row gap-6 mb-6">
            <!-- Image Upload (Full width on mobile, 1/3 on desktop) -->
            <div class="w-full md:w-1/3">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center h-full">
                <div v-if="imagePreview" class="mb-4">
                    <img :src="imagePreview" alt="Product preview" class="w-full h-48 object-contain mx-auto rounded-md">
                    <button
                    @click="removeImage"
                    type="button"
                    class="mt-2 text-sm text-red-600 hover:text-red-800"
                    >
                    Remove Image
                    </button>
                </div>
                <div v-else class="py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-1 text-sm text-gray-600">Upload a product image</p>
                </div>
                <label class="mt-2 cursor-pointer">
                    <span class="sr-only">Choose product image</span>
                    <input
                    type="file"
                    @change="handleImageUpload"
                    accept="image/*"
                    class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-gray-50 file:text-gray-700
                            hover:file:bg-gray-100"
                    >
                </label>
                </div>
            </div>

            <!-- Name and Product Type (Full width on mobile, 2/3 on desktop) -->
            <div class="w-full md:w-2/3 space-y-4">
                <label class="block">
                <span class="block text-sm font-medium text-gray-700 mb-1">Name *</span>
                <input
                    v-model="selectedProduct.name"
                    type="text"
                    placeholder="Product name"
                    required
                    class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                        transition-colors duration-200"
                />
                </label>

                <label class="block">
                <span class="block text-sm font-medium text-gray-700 mb-1">Product Type</span>
                <select
                    v-model="selectedProduct.store_product_type_id"
                    class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                        transition-colors duration-200"
                >
                    <option value="">Select a type</option>
                    <option
                    v-for="type in productTypes"
                    :key="type.id"
                    :value="type.id"
                    >
                    {{ type.name }}
                    </option>
                </select>
                </label>
            </div>
            </div>

            <!-- Second Row - Barcode, Unit, Low Stock Alert -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <label class="block">
                <span class="block text-sm font-medium text-gray-700 mb-1">Barcode</span>
                <input
                v-model="selectedProduct.barcode"
                type="text"
                placeholder="Barcode"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                        transition-colors duration-200"
                />
            </label>

            <label class="block">
                <span class="block text-sm font-medium text-gray-700 mb-1">Unit</span>
                <select
                v-model="selectedProduct.unit"
                type="text"
                placeholder="Unit (e.g., kg, pcs)"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                        transition-colors duration-200"
                >
                <option value="">Select a unit</option>
                <option value="kg">Kilogram (kg)</option>
                <option value="pcs">Piece (pcs)</option>
                <option value="g">Gram (g)</option>
                <option value="ml">Milliliter (ml)</option>
                <option value="liter">Liter (l)</option>
                </select>
            </label>

            <label class="block">
                <span class="block text-sm font-medium text-gray-700 mb-1">Low Stock Alert</span>
                <input
                v-model="selectedProduct.low_stock_alert"
                type="number"
                min="0"
                placeholder="10"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                        transition-colors duration-200"
                />
            </label>
            </div>

            <!-- Third Row - Description -->
            <div class="mb-6">
            <label class="block">
                <span class="block text-sm font-medium text-gray-700 mb-1">Description</span>
                  <ckeditor :editor="editor" v-model="selectedProduct.description"
                :config="{ toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList'] }"
                />

            </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
            <button
                @click="closeModal"
                class="px-4 py-2 rounded-md border text-sm font-medium
                    border-gray-300 text-gray-700 hover:bg-gray-50
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                    transition-colors duration-200"
            >
                Cancel
            </button>
            <button
                @click="saveProduct"
                class="px-5 py-2 rounded-md text-sm font-medium shadow-sm
                    bg-gray-800 text-white hover:bg-gray-900
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                    transition-colors duration-200"
            >
                Save
            </button>
            </div>
        </div>
        </div>

      <!-- Delete Confirmation Modal -->
      <div
        v-if="isDeleting"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
      >
        <!-- Glass overlay -->
        <div
          class="fixed inset-0 backdrop-blur-sm"
          @click="closeDeleteConfirm"
        ></div>

        <!-- Modal content -->
        <div
          class="relative bg-white/90 backdrop-blur-md rounded-lg p-6 w-full max-w-sm shadow-xl border border-white/20"
        >
          <h3 class="text-lg font-semibold text-gray-900 mb-4">
            Confirm Delete
          </h3>
          <p class="mb-6 text-gray-700">
            Are you sure you want to delete
            <strong class="text-gray-900">{{ selectedProduct?.name }}</strong>?
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="closeDeleteConfirm"
              class="px-4 py-2 rounded-md border text-sm font-medium
                     border-gray-300 text-gray-700 hover:bg-gray-50
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                     transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              @click="deleteProduct"
              class="px-5 py-2 rounded-md text-sm font-medium shadow-sm
                     bg-red-600 text-white hover:bg-red-700
                     focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2
                     transition-colors duration-200"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
