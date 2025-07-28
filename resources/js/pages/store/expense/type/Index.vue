<script setup>
import { ref, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";

const props = defineProps({
  expenseTypes: Object,
});

console.log(props.expenseTypes);

const breadcrumbs = [{ title: "Expense Types", href: "/store/expense-types" }];

const searchQuery = ref("");
const selectedExpenseType = ref(null);
const isModalOpen = ref(false);
const isEditing = ref(false);
const isDeleting = ref(false);

const filteredExpenseTypes = computed(() => {
  if (!searchQuery.value) return props.expenseTypes.data;

  return props.expenseTypes.data.filter((et) =>
    et.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

function openAddModal() {
  selectedExpenseType.value = { name: "", description: "" };
  isModalOpen.value = true;
  isEditing.value = false;
}

function openEditModal(expenseType) {
  console.log(expenseType.name);
  console.log(expenseType.description);

  selectedExpenseType.value = { ...expenseType };
  isModalOpen.value = true;
  isEditing.value = true;
}

function closeModal() {
  isModalOpen.value = false;
}

function saveExpenseType() {
  if (!selectedExpenseType.value.name.trim()) {
    alert("Name is required");
  }
  if (isEditing.value) {
    router.put(
      `/store/expense-types/${selectedExpenseType.value.id}`,
      selectedExpenseType.value,
      {
        onSuccess: () => closeModal(),
      }
    );
  } else {
    router.post("/store/expense-types", selectedExpenseType.value, {
      onSuccess: () => closeModal(),
    });
  }
}

function openDeleteConfirm(expenseType) {
  selectedExpenseType.value = { ...expenseType };
  console.log(selectedExpenseType.value);
  console.log(selectedExpenseType.value.id);
  isDeleting.value = true;
}

function closeDeleteConfirm() {
  isDeleting.value = false;
  selectedExpenseType.value = null;
}

function deleteExpenseType() {
  if (!selectedExpenseType.value?.id) return;

  router.delete(`/store/expense-types/${selectedExpenseType.value.id}`, {
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
  <Head title="Expense Types" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Search & Add Button -->
      <div
        class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search expense types..."
          class="sm:w-64 px-4 py-2 border rounded-md bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
        />
        <button
          @click="openAddModal"
          class="inline-flex items-center justify-center px-5 py-2 bg-gray-700 text-white font-semibold rounded-md shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
        >
          + Add Expense Type
        </button>
      </div>

      <!-- Product Table -->
      <div class="overflow-x-auto border rounded-lg border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
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
              v-for="expenseType in filteredExpenseTypes"
              :key="expenseType.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ expenseType.name }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-700">
                {{ expenseType.description }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ new Date(expenseType.created_at).toLocaleDateString() }}
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3"
              >
                <button
                  @click="openEditModal(expenseType)"
                  class="text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 rounded"
                  aria-label="Edit"
                >
                  Edit
                </button>
                <button
                  @click="openDeleteConfirm(expenseType)"
                  class="text-red-600 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 rounded"
                  aria-label="Delete"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="filteredExpenseTypes.length === 0">
              <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                No expense types found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav class="mt-6 flex justify-center space-x-2" aria-label="Pagination">
        <button
          v-for="link in props.expenseTypes.links"
          :key="link.label"
          :disabled="!link.url"
          @click.prevent="goToPage(link.url)"
          class="px-4 py-2 border rounded-md text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-colors duration-200"
          :class="{
            'bg-blue-50 border-blue-500 text-blue-600': link.active,
          }"
          v-html="link.label"
          aria-current="page"
        ></button>
      </nav>

      <!-- Add Modal -->
      <div
        v-if="isModalOpen"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
      >
        <!-- Glass overlay -->
        <div class="fixed inset-0 backdrop-blur-sm" @click="closeModal"></div>

        <!-- Modal content -->
        <div
          class="relative bg-white/90 backdrop-blur-md rounded-lg p-6 w-full max-w-md shadow-xl border border-white/20"
        >
          <h2 class="text-xl font-semibold mb-4 text-gray-900">
            {{ isEditing ? "Edit" : "Add" }} Product Type
          </h2>

          <label class="block mb-4">
            <span class="block text-sm font-medium text-gray-700 mb-1">Name *</span>
            <input
              v-model="selectedExpenseType.name"
              type="text"
              placeholder="Enter name"
              required
              class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            />
          </label>

          <label class="block mb-6">
            <span class="block text-sm font-medium text-gray-700 mb-1">Description</span>
            <textarea
              v-model="selectedExpenseType.description"
              rows="3"
              placeholder="Enter description"
              class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            ></textarea>
          </label>

          <div class="flex justify-end space-x-3">
            <button
              @click="closeModal"
              class="px-4 py-2 rounded-md border text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              @click="saveExpenseType"
              class="px-5 py-2 rounded-md text-sm font-medium shadow-sm bg-gray-800 text-white hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Save
            </button>
          </div>
        </div>
      </div>
      <div
        v-if="isDeleting"
        class="fixed inset-0 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
      >
        <!-- Glass overlay -->
        <div class="fixed inset-0 backdrop-blur-sm" @click="closeDeleteConfirm"></div>

        <!-- Modal content -->
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
              @click="deleteExpenseType"
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
