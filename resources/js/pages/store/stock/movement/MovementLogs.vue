<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Props from Inertia
const props = defineProps({
    stock_movements: Array,
});

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Movement', href: '/store/stock-lists' },
];

function goToPage(url) {
    console.log(url);
    if (!url) return;
    router.get(url);
}
</script>

<template>
    <Head title="Reports" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Summary Report Table -->
            <div class="rounded-lg border border-gray-300 bg-white p-6 shadow-sm">
                <h2 class="mb-6 flex items-center gap-3 text-2xl font-bold text-gray-900 sm:text-3xl">
                    <span class="text-gray-800">Stock Movements</span>
                </h2>

                <div class="overflow-x-auto border border-gray-300 shadow-sm">
                    <table class="min-w-full border-collapse font-mono text-sm">
                        <!-- Table Header -->
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="sticky top-0 border border-gray-300 bg-gray-100 px-4 py-2 text-center font-medium text-gray-700">
                                    Stock Number
                                </th>

                                <th class="sticky top-0 border border-gray-300 bg-gray-100 px-4 py-2 text-center font-medium text-gray-700">
                                    Product Image
                                </th>

                                <th class="sticky top-0 border border-gray-300 bg-gray-100 px-4 py-2 text-center font-medium text-gray-700">
                                    PRoduct Name
                                </th>

                                <th class="sticky top-0 border border-gray-300 bg-gray-100 px-4 py-2 text-center font-medium text-gray-700">
                                    Quantity
                                </th>

                                <th class="sticky top-0 border border-gray-300 bg-gray-100 px-4 py-2 text-center font-medium text-gray-700">
                                    Source Type
                                </th>

                                <th class="sticky top-0 border border-gray-300 bg-gray-100 px-4 py-2 text-center font-medium text-gray-700">Date</th>
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody>
                            <tr v-for="item in props.stock_movements.data" :key="item.id" class="hover:bg-gray-50">
                                <!-- Table rows remain unchanged -->
                                <td class="min-w-[120px] border border-gray-300 bg-white px-7 py-2 text-left font-medium whitespace-nowrap">
                                    {{ item.invoice_number }}
                                </td>

                                <td class="border border-gray-300 bg-white px-4 py-2 text-center">
                                    <img
                                        :src="
                                            item.media_name ? `/storage/${item.media_path}/${item.media_name}` : '/assets/default/default_product.png'
                                        "
                                        alt="Product Image"
                                        class="mx-auto h-16 w-16 object-cover"
                                    />
                                </td>

                                <td class="border border-gray-300 bg-gray-50 px-4 py-2 text-right font-semibold">
                                    {{ item.product_name }}
                                </td>

                                <td class="border border-gray-300 bg-white px-4 py-2 text-right text-red-600">
                                    {{ item.quantity }}
                                </td>

                                <td class="border border-gray-300 bg-gray-50 px-4 py-2 text-right font-semibold">
                                    {{ item.source_type }}
                                </td>

                                <td class="min-w-[120px] border border-gray-300 bg-white px-7 py-2 text-left font-medium whitespace-nowrap">
                                    {{ humanTime(item.movement_date) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav class="mt-6 flex justify-center space-x-2" aria-label="Pagination">
                    <button
                        v-for="link in props.stock_movements.links"
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
            </div>
        </div>
    </AppLayout>
</template>
