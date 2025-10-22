<!-- components/InvoicePrint.vue -->
<template>
  <div class="invoice p-4 font-mono text-sm" v-show="visible">
    <h2 class="text-center font-bold">{{ settings.business_title || 'Store Name' }}</h2>
    <p class="text-center">{{ settings.address }} | {{ settings.phone }}</p>
    <p>Date: {{ new Date().toLocaleString() }}</p>
    <p>Customer: {{ customer?.name || 'Walking Customer' }}</p>
    <p>Invoice #: INV-{{ Date.now() }}</p>

    <table class="w-full border-collapse mt-2 mb-2">
      <thead>
        <tr class="bg-gray-100">
          <th class="border px-1 py-1">Item</th>
          <th class="border px-1 py-1">Price</th>
          <th class="border px-1 py-1">Qty</th>
          <th class="border px-1 py-1">Total</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td class="border px-1 py-1">{{ item.product.name }}</td>
          <td class="border px-1 py-1">{{ formatCurrency(item.price) }}</td>
          <td class="border px-1 py-1">{{ item.quantity }}</td>
          <td class="border px-1 py-1">{{ formatCurrency(item.price * item.quantity) }}</td>
        </tr>
      </tbody>
    </table>

    <div class="mt-2">
      <div class="flex justify-between"><span>Total:</span><span>{{ formatCurrency(total) }}</span></div>
      <div class="flex justify-between"><span>Paid:</span><span>{{ formatCurrency(paid) }}</span></div>
      <div class="flex justify-between"><span>Due:</span><span>{{ formatCurrency(due) }}</span></div>
      <div class="flex justify-between"><span>Payment Method:</span><span>{{ paymentMethodName }}</span></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import StoreSetting from '@/utils/module/StoreSetting';
import { formatCurrency } from '@/utils/helper';

const props = defineProps({
  items: Array,
  customer: Object,
  total: Number,
  paid: Number,
  due: Number,
  paymentMethod: String,
  paymentMethods: Array
});

const settings = StoreSetting.all.value;
const visible = ref(false);

const paymentMethodName = computed(() => {
  return props.paymentMethods.find(p => p.id === props.paymentMethod)?.name || '';
});

// Expose show() method to parent
function show() {
  visible.value = true;
  setTimeout(() => {
    window.print();
    visible.value = false;
  }, 100);
}

defineExpose({ show, formatCurrency });
</script>
<style scoped>
@media print {
  body * {
    visibility: hidden; /* hide everything */
  }

  .invoice, .invoice * {
    visibility: visible; /* show only invoice */
  }

  .invoice {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}

</style>
