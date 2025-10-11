<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

    const props = defineProps({
        grands: Array,
    });

console.log(props.grands);

  // Breadcrumbs
  const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assets', href: '/store/total-asset' },
  ];
</script>
<template>
  <Head title="Stock Reports" />
  <AppLayout :breadcrumbs="breadcrumbs">
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center p-6">
    <div class="max-w-3xl w-full">
      <h1 class="text-3xl font-bold text-gray-800 text-center mb-8">
        💰 Total Asset Summary
      </h1>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Buy Asset -->
        <div
          class="bg-white border border-gray-200 shadow-lg hover:shadow-xl transition rounded-2xl p-6 flex flex-col items-center"
        >
          <span class="text-sm uppercase tracking-wide text-gray-500 mb-2">
            Remaining Purchase Asset
          </span>
          <span
            class="text-4xl font-extrabold text-emerald-600 drop-shadow-sm"
          >
            ৳{{ grands.grand_availble_asset_buy_price.toLocaleString() }}
          </span>
        </div>

        <!-- Sale Asset -->
        <div
          class="bg-white border border-gray-200 shadow-lg hover:shadow-xl transition rounded-2xl p-6 flex flex-col items-center"
        >
          <span class="text-sm uppercase tracking-wide text-gray-500 mb-2">
            Remaining Sale Asset
          </span>
          <span
            class="text-4xl font-extrabold text-indigo-600 drop-shadow-sm"
          >
            ৳{{ grands.grand_availble_asset_sale_price.toLocaleString() }}
          </span>
        </div>
      </div>

      <div class="mt-10 text-center text-gray-500 text-sm">
        Updated: {{ new Date().toLocaleString() }}
      </div>
    </div>
  </div>
  </AppLayout>
</template>

