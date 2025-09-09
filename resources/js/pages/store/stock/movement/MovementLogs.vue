<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";
import dayjs from "dayjs";
import relativeTime from "dayjs/plugin/relativeTime";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
import "@vuepic/vue-datepicker/dist/main.css";

// Extend Day.js plugins
dayjs.extend(relativeTime);
dayjs.extend(utc);
dayjs.extend(timezone);

// Props from Inertia
const props = defineProps({
  stock_movements: Array,
});

// Reactive now for live updates
const now = ref(dayjs());

// Update every 30 seconds for live "x minutes ago"
let timer = null;
onMounted(() => {
  timer = setInterval(() => {
    now.value = dayjs();
  }, 30000);
});

onUnmounted(() => {
  clearInterval(timer);
});

// Helper to format timestamps
const humanTime = (date) => {
  const dt = dayjs.utc(date).tz("Asia/Dhaka"); // Convert UTC to Dhaka time
  const nowTime = dayjs().tz("Asia/Dhaka");

  // If older than 7 days, show absolute date & time
  if (nowTime.diff(dt, "day") >= 7) {
    return dt.format("YYYY-MM-DD HH:mm"); // e.g., 2025-09-01 14:23
  }

  // Otherwise show relative time
  return dt.fromNow();
};

// Breadcrumbs
const breadcrumbs = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Movement", href: "/store/stock-lists" },
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
    <div class="p-6 space-y-8">
        
      <!-- Summary Report Table -->
      <div class="bg-white border border-gray-300 rounded-lg p-6 shadow-sm">
        <h2
          class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 flex items-center gap-3"
        >
          <span class="text-gray-800">Stock Movements</span>
        </h2>

        <div class="overflow-x-auto border border-gray-300 shadow-sm">
          <table class="min-w-full border-collapse text-sm font-mono">
            <!-- Table Header -->
            <thead class="bg-gray-100">
              <tr>
                <th
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  Stock Number
                </th>

                <th
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  Product Image
                </th>

                <th
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  PRoduct Name
                </th>

                <th
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  Quantity
                </th>

                <th
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  Source Type
                </th>

                <th
                  class="sticky top-0 border border-gray-300 px-4 py-2 text-center font-medium text-gray-700 bg-gray-100"
                >
                  Date
                </th>
              </tr>
            </thead>

            <!-- Table Body -->
            <tbody>
              <tr
                v-for="item in props.stock_movements.data"
                :key="item.id"
                class="hover:bg-gray-50"
              >
                <!-- Table rows remain unchanged -->
                <td class="border border-gray-300 px-7 py-2 text-left font-medium bg-white whitespace-nowrap min-w-[120px]">
                  {{ item.invoice_number }}
                </td>

                <td class="border border-gray-300 px-4 py-2 bg-white text-center">
                  <img 
                    :src="item.media_name 
                      ? `/storage/${item.media_path}/${item.media_name}` 
                      : '/assets/default/default_product.png'" 
                    alt="Product Image" 
                    class="w-16 h-16 object-cover mx-auto"
                  />
                </td>

                <td class="border border-gray-300 px-4 py-2 text-right font-semibold bg-gray-50">
                  {{ item.product_name }}
                </td>

                <td class="border border-gray-300 px-4 py-2 text-right text-red-600 bg-white">
                  {{ item.quantity }}
                </td>

                <td class="border border-gray-300 px-4 py-2 text-right font-semibold bg-gray-50">
                  {{ item.source_type }}
                </td>

                <td class="border border-gray-300 px-7 py-2 text-left font-medium bg-white whitespace-nowrap min-w-[120px]">
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
            class="px-4 py-2 border rounded-md text-sm font-medium border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-colors duration-200"
            :class="{
              'bg-blue-50 border-blue-500 text-blue-600': link.active,
            }"
            v-html="link.label"
            aria-current="page"
          ></button>
        </nav>

      </div>
    </div>
  </AppLayout>
</template>
