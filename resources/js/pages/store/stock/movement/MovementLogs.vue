<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
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
              v-for="item in props.stock_movements"
              :key="item"
                class="hover:bg-gray-50"
              >
                <!-- Date -->
                <td
                  class="border border-gray-300 px-7 py-2 text-left font-medium bg-white whitespace-nowrap min-w-[120px]"
                >
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


                <!-- Total Sales -->
                <td
                  class="border border-gray-300 px-4 py-2 text-right font-semibold bg-gray-50"
                >
                   {{ item.product_name }}
                </td>

                <!-- Cost per category -->
                <td
                  class="border border-gray-300 px-4 py-2 text-right text-red-600 bg-white"
                >
                   {{ item.quantity }}
                </td>

                <!-- Total Cost -->
                <td
                  class="border border-gray-300 px-4 py-2 text-right font-semibold bg-gray-50"
                >
                     {{ item.source_type }}
                </td>

                <td
                  class="border border-gray-300 px-7 py-2 text-left font-medium bg-white whitespace-nowrap min-w-[120px]"
                >
                  {{ humanTime(item.movement_date) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
