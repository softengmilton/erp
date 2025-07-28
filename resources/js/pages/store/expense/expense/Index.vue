<script setup>
import { computed, ref } from "vue";
import { Head, router } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";

const editor = ClassicEditor;
const props = defineProps({
  expenses: Object,
  expenseTypes: Array,
});

const selectedExpense = ref(null);
const isModalOpen = ref(false);
const expenseFile = ref(null);
const searchQuery = ref(null);
const isEditing = ref(false);
const isDeleting = ref(false);

const breadcrumbs = [{ title: "Expenses", href: "/store/expenses" }];

const filteredExpenses = computed(() => {
  if (!searchQuery.value) return props.expenses.data;

  return props.expenses.data.filter((e) =>
    e.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

function openAddModal() {
  selectedExpense.value = {
    name: "",
    store_expense_type_id: "",
    amount: "",
    description: "",
  };

  isModalOpen.value = true;
  isEditing.value = false;
}

function openEditModal(expense) {
  console.log(expense.name);
  selectedExpense.value = { ...expense };
  isModalOpen.value = true;
  isEditing.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  console.log("1");
}

function handleFileUpload(event) {
  const file = event.target.files[0];

  if (!file) return;

  expenseFile.value = file;
}

async function saveExpense() {
  const formData = new FormData();

  // Append all expense data
  for (const key in selectedExpense.value) {
    if (selectedExpense.value[key] !== null) {
      formData.append(key, selectedExpense.value[key]);
    }
  }

  // Append file if exists
  if (expenseFile.value) {
    formData.append("attachment", expenseFile.value);
  }

  if (isEditing.value) {
    formData.append("_method", "PUT");
    await router.post(`/store/expenses/${selectedExpense.value.id}`, formData, {
      onSuccess: () => closeModal(),
      forceFormData: true,
    });
  } else {
    await router.post("/store/expenses", formData, {
      onSuccess: () => closeModal(),
      forceFormData: true,
    });
  }
}

function openDeleteModal(expense) {
  selectedExpense.value = { ...expense };
  isDeleting.value = true;
}
function closeDeleteConfirm() {
  isDeleting.value = false;
}

function deleteExpense() {
  if (!selectedExpense.value?.id) return;
  router.delete(`/store/expenses/${selectedExpense.value.id}`, {
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
  <Head title="Expenses" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div
        class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search expenses..."
          class="sm:w-64 px-4 py-2 border rounded-md bg-white text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
        />
        <button
          @click="openAddModal"
          class="inline-flex items-center justify-center px-5 py-2 bg-gray-700 text-white font-semibold rounded-md shadow-sm hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
        >
          + Add Expense
        </button>
      </div>

      <div class="overflow-x-auto border rounded-lg border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Expense Name
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Expense Type
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Amount
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
              >
                Description
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
              v-for="expense in filteredExpenses"
              :key="expense.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ expense.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ expense.store_expense_type.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ expense.amount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                {{ expense.description }}
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3"
              >
                <button
                  @click="openEditModal(expense)"
                  class="text-blue-600 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 rounded"
                >
                  Edit
                </button>
                <button
                  @click="openDeleteModal(expense)"
                  class="text-red-600 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 rounded"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="filteredExpenses.length === 0">
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
          v-for="link in props.expenses.links"
          :key="link.label"
          :disabled="!link.url"
          @click.prevent="goToPage(link.url)"
          class="px-4 py-2 border rounded-md text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-colors duration-200"
          :class="{
            'bg-blue-50 border-blue-500 text-blue-600': link.active,
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
        <div class="fixed inset-0 backdrop-blur-sm" @click="closeModal"></div>
        <!-- Modal content -->
        <div
          class="relative bg-white/90 backdrop-blur-md rounded-lg p-6 w-full max-w-4xl shadow-xl border border-white/20 overflow-y-auto max-h-[90vh]"
        >
          <h2 class="text-xl font-semibold mb-4 text-gray-900">
            {{ isEditing ? "Edit" : "+ Add" }} Expense
          </h2>
          <!-- First Row - Image and Name -->
          <div class="flex flex-col md:flex-row gap-6 mb-6">
            <!-- Name and Product Type -->
            <div class="w-full md:w-2/3 space-y-4">
              <label class="block">
                <span class="block text-sm font-medium text-gray-700 mb-1">Name *</span>
                <input
                  v-model="selectedExpense.name"
                  type="text"
                  placeholder="Product name"
                  required
                  class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                />
              </label>
              <label class="block">
                <span class="block text-sm font-medium text-gray-700 mb-1"
                  >Expense Type</span
                >
                <select
                  v-model="selectedExpense.store_expense_type_id"
                  class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                >
                  <option value="">Select a type</option>
                  <option
                    v-for="type in props.expenseTypes"
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
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <label class="block w-full">
              <span class="block text-sm font-medium text-gray-700 mb-1">Amount</span>
              <input
                v-model="selectedExpense.amount"
                type="number"
                placeholder="Amount"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              />
            </label>
            <label class="block w-full">
              <span class="block text-sm font-medium text-gray-700 mb-1">Attachment</span>
              <input
                @change="handleFileUpload"
                type="file"
                placeholder="Attachment"
                class="mt-1 block w-full rounded-md shadow-sm border border-gray-300 px-3 py-2 bg-white/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              />
            </label>
          </div>
          <!-- Third Row - Description -->
          <div class="mb-6">
            <label class="block">
              <span class="block text-sm font-medium text-gray-700 mb-1"
                >Description</span
              >
              <ckeditor
                :editor="editor"
                v-model="selectedExpense.description"
                :config="{
                  toolbar: ['bold', 'italic', 'link', 'bulletedList', 'numberedList'],
                }"
              />
            </label>
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
              @click="saveExpense"
              class="px-5 py-2 rounded-md text-sm font-medium shadow-sm bg-gray-800 text-white hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
            >
              Save
            </button>
            <button @click="consoleLog">console log</button>
          </div>
        </div>
        <!--  Modal content ends here -->
      </div>
      <!--  Entire modal wrapper ends here -->
      <!-- Delete Confirmation Modal -->
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
            <strong class="text-gray-900">{{ selectedProduct?.name }}</strong
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
              @click="deleteExpense"
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
