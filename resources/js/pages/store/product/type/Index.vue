<script setup>
import { ref, computed } from "vue";
import { router, Head } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";

const props = defineProps({
  productTypes: Object, // Laravel paginator object
});

const breadcrumbs = [{ title: "Product Types", href: "/store/product-types" }];

const searchQuery = ref("");
const selectedProductType = ref(null);
const isModalOpen = ref(false);
const isEditing = ref(false);
const isDeleting = ref(false);
const imagePreview = ref(null);
const imageFile = ref(null);

const filteredProductTypes = computed(() => {
  if (!searchQuery.value) return props.productTypes.data;
  return props.productTypes.data.filter((pt) =>
    pt.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

function openAddModal() {
  selectedProductType.value = { name: "", description: "", image_url: null };
  imagePreview.value = null;
  imageFile.value = null;
  isEditing.value = false;
  isModalOpen.value = true;
}

function openEditModal(productType) {
  selectedProductType.value = { ...productType };
  imagePreview.value = productType.primary_image_url || null;
  imageFile.value = null;
  isEditing.value = true;
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  selectedProductType.value = null;
  imagePreview.value = null;
  imageFile.value = null;
}

function handleImageUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  imageFile.value = file;

  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
}

function removeImage() {
  imagePreview.value = null;
  imageFile.value = null;
  if (selectedProductType.value.image_url) {
    selectedProductType.value.image_url = null;
  }
}

function saveProductType() {
  if (!selectedProductType.value.name.trim()) {
    alert("Name is required");
    return;
  }

  const formData = new FormData();

  for (const key in selectedProductType.value) {
    if (selectedProductType.value[key] !== null) {
      formData.append(key, selectedProductType.value[key]);
    }
  }

  if (imageFile.value) {
    formData.append("image", imageFile.value);
  }

  if (isEditing.value) {
    formData.append("_method", "PUT");
    router.post(`/store/product-types/${selectedProductType.value.id}`, formData, {
      onSuccess: () => closeModal(),
      forceFormData: true,
    });
  } else {
    router.post("/store/product-types", formData, {
      onSuccess: () => closeModal(),
      forceFormData: true,
    });
  }
}

function openDeleteConfirm(productType) {
  selectedProductType.value = { ...productType };
  isDeleting.value = true;
}

function closeDeleteConfirm() {
  isDeleting.value = false;
  selectedProductType.value = null;
}

function deleteProductType() {
  if (!selectedProductType.value?.id) return;
  router.delete(`/store/product-types/${selectedProductType.value.id}`, {
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
  <Head title="Product Types" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Top Bar -->
      <div
        class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search product types..."
          class="sm:w-64 px-4 py-2 border rounded-md bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
        />
        <button
          @click="openAddModal"
          class="inline-flex items-center justify-center px-5 py-2 bg-gray-700 text-white font-semibold rounded-md shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
        >
          + Add Product Type
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto border rounded-lg border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Image
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Name
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Description
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Created At
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="productType in filteredProductTypes"
              :key="productType.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4">
                <img
                  :src="productType.primary_image_url"
                  alt="Preview"
                  class="w-12 h-12 rounded object-cover"
                />
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ productType.name }}
              </td>
              <td class="px-6 py-4 whitespace-normal text-sm text-gray-700">
                {{ productType.description || "-" }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ new Date(productType.created_at).toLocaleDateString() }}
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3"
              >
                <button
                  @click="openEditModal(productType)"
                  class="text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 rounded"
                >
                  Edit
                </button>
                <button
                  @click="openDeleteConfirm(productType)"
                  class="text-red-600 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 rounded"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="filteredProductTypes.length === 0">
              <td colspan="5" class="px-6 py-6 text-center text-gray-500">
                No product types found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav class="mt-6 flex justify-center space-x-2" aria-label="Pagination">
        <button
          v-for="link in props.productTypes.links"
          :key="link.label"
          :disabled="!link.url"
          @click.prevent="goToPage(link.url)"
          class="px-4 py-2 border rounded-md text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-colors duration-200"
          :class="{ 'bg-blue-50 border-blue-500 text-blue-600': link.active }"
          v-html="link.label"
        ></button>
      </nav>

      <!-- Add/Edit Modal -->
      <div
        v-if="isModalOpen"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
      >
        <div class="fixed inset-0 backdrop-blur-sm" @click="closeModal"></div>
        <div
          class="relative bg-white/90 backdrop-blur-md rounded-lg p-6 w-full max-w-lg shadow-xl border border-white/20"
        >
          <h2 class="text-xl font-semibold mb-4 text-gray-900">
            {{ isEditing ? "Edit" : "Add" }} Product Type
          </h2>
          <!-- Name -->
          <label class="block mb-4">
            <span class="block text-sm font-medium text-gray-700 mb-1">Name *</span>
            <input
              v-model="selectedProductType.name"
              type="text"
              placeholder="Enter name"
              required
              class="block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            />
          </label>

          <!-- Description -->
          <label class="block mb-6">
            <span class="block text-sm font-medium text-gray-700 mb-1">Description</span>
            <textarea
              v-model="selectedProductType.description"
              rows="3"
              placeholder="Enter description"
              class="block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            ></textarea>
          </label>
          <!-- Image Upload -->
          <div class="mb-4">
            <!-- Image Upload (Full width on mobile, 1/3 on desktop) -->
            <div class="w-full md:w-1/3 h-48">
              <div
                class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center h-full"
              >
                <div v-if="imagePreview" class="mb-4 flex flex-col h-full">
                  <div class="overflow-hidden rounded-md flex-grow">
                    <img
                      :src="imagePreview"
                      alt="Product preview"
                      class="w-full h-full object-contain mx-auto rounded-md"
                    />
                  </div>
                  <div class="mt-2 flex justify-center space-x-2">
                    <button
                      @click="removeImage"
                      type="button"
                      class="px-3 py-1 text-sm text-red-600 hover:text-red-800 border border-red-600 rounded"
                    >
                      Remove Image
                    </button>
                  </div>
                </div>
                <div v-else class="h-full flex flex-col justify-between">
                  <div class="py-4">
                    <svg
                      class="mx-auto h-12 w-12 text-gray-400"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                      />
                    </svg>
                    <p class="mt-1 text-sm text-gray-600">Upload a product image</p>
                  </div>
                  <label class="cursor-pointer">
                    <span class="sr-only">Choose product image</span>
                    <input
                      type="file"
                      @change="handleImageUpload"
                      accept="image/*"
                      class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100"
                    />
                  </label>
                </div>
              </div>
            </div>
          </div>
          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3">
            <button
              @click="closeModal"
              class="px-4 py-2 rounded-md border text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              @click="saveProductType"
              class="px-5 py-2 rounded-md text-sm font-medium shadow-sm bg-gray-800 text-white hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Save
            </button>
          </div>
        </div>
      </div>

      <!-- Delete Modal -->
      <div
        v-if="isDeleting"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
      >
        <div class="fixed inset-0 backdrop-blur-sm" @click="closeDeleteConfirm"></div>
        <div
          class="relative bg-white/90 backdrop-blur-md rounded-lg p-6 w-full max-w-sm shadow-xl border border-white/20"
        >
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Delete</h3>
          <p class="mb-6 text-gray-700">
            Are you sure you want to delete
            <strong class="text-gray-900">{{ selectedProductType?.name }}</strong
            >?
          </p>
          <div class="flex justify-end space-x-3">
            <button
              @click="closeDeleteConfirm"
              class="px-4 py-2 rounded-md border text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              @click="deleteProductType"
              class="px-5 py-2 rounded-md text-sm font-medium shadow-sm bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
