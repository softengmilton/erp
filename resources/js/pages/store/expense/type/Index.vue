<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    expenseTypes: Object,
});

console.log(props.expenseTypes);

const breadcrumbs = [{ title: 'Expense Types', href: '/store/expense-types' }];

const searchQuery = ref('');
const selectedExpenseType = ref(null);
const isModalOpen = ref(false);
const isEditing = ref(false);
const isDeleting = ref(false);

const filteredExpenseTypes = computed(() => {
    if (!searchQuery.value) return props.expenseTypes.data;

    return props.expenseTypes.data.filter((et) => et.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

function openAddModal() {
    selectedExpenseType.value = { name: '', description: '' };
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
        alert('Name is required');
    }
    if (isEditing.value) {
        router.put(`/store/expense-types/${selectedExpenseType.value.id}`, selectedExpenseType.value, {
            onSuccess: () => closeModal(),
        });
    } else {
        router.post('/store/expense-types', selectedExpenseType.value, {
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
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search expense types..."
                    class="rounded-md border bg-white px-4 py-2 text-gray-900 placeholder-gray-500 transition-colors duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none sm:w-64"
                />
                <button
                    @click="openAddModal"
                    class="inline-flex items-center justify-center rounded-md bg-gray-700 px-5 py-2 font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                >
                    + Add Expense Type
                </button>
            </div>

            <!-- Product Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">Created At</th>
                            <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="expenseType in filteredExpenseTypes" :key="expenseType.id" class="transition-colors duration-150 hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900">
                                {{ expenseType.name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ expenseType.description }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ new Date(expenseType.created_at).toLocaleDateString() }}
                            </td>
                            <td class="space-x-3 px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                <template v-if="expenseType.name.toLowerCase() !== 'assets'">
                                    <button
                                        @click="openEditModal(expenseType)"
                                        class="rounded text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:outline-none"
                                        aria-label="Edit"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="openDeleteConfirm(expenseType)"
                                        class="rounded text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 focus:ring-offset-1 focus:outline-none"
                                        aria-label="Delete"
                                    >
                                        Delete
                                    </button>
                                </template>
                            </td>
                        </tr>
                        <tr v-if="filteredExpenseTypes.length === 0">
                            <td colspan="4" class="px-6 py-6 text-center text-gray-500">No expense types found.</td>
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
                    class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    :class="{
                        'border-blue-500 bg-blue-50 text-blue-600': link.active,
                    }"
                    v-html="link.label"
                    aria-current="page"
                ></button>
            </nav>

            <!-- Add Modal -->
            <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <!-- Glass overlay -->
                <div class="fixed inset-0 backdrop-blur-sm" @click="closeModal"></div>

                <!-- Modal content -->
                <div class="relative w-full max-w-md rounded-lg border border-white/20 bg-white/90 p-6 shadow-xl backdrop-blur-md">
                    <h2 class="mb-4 text-xl font-semibold text-gray-900">{{ isEditing ? 'Edit' : 'Add' }} Expense Type</h2>

                    <label class="mb-4 block">
                        <span class="mb-1 block text-sm font-medium text-gray-700">Name *</span>
                        <input
                            v-model="selectedExpenseType.name"
                            type="text"
                            placeholder="Enter name"
                            required
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white/80 px-3 py-2 shadow-sm transition-colors duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        />
                    </label>

                    <label class="mb-6 block">
                        <span class="mb-1 block text-sm font-medium text-gray-700">Description</span>
                        <textarea
                            v-model="selectedExpenseType.description"
                            rows="3"
                            placeholder="Enter description"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white/80 px-3 py-2 shadow-sm transition-colors duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        ></textarea>
                    </label>

                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeModal"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Cancel
                        </button>
                        <button
                            @click="saveExpenseType"
                            class="rounded-md bg-gray-800 px-5 py-2 text-sm font-medium text-white shadow-sm transition-colors duration-200 hover:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Save
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="isDeleting" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <!-- Glass overlay -->
                <div class="fixed inset-0 backdrop-blur-sm" @click="closeDeleteConfirm"></div>

                <!-- Modal content -->
                <div class="relative w-full max-w-sm rounded-lg border border-white/20 bg-white/90 p-6 shadow-xl backdrop-blur-md">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Confirm Delete</h3>
                    <p class="mb-6 text-gray-700">
                        Are you sure you want to delete
                        <strong class="text-gray-900">{{ selectedProductType?.name }}</strong
                        >?
                    </p>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeDeleteConfirm"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Cancel
                        </button>
                        <button
                            @click="deleteExpenseType"
                            class="rounded-md bg-red-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition-colors duration-200 hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
