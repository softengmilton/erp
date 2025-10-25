<template>
  <div class="receipt font-mono text-[13px]" v-show="visible">
    <!-- Header -->
    <div class="header mb-3">
      <h2 class="business-title">
        {{ settings.business_title || 'SHOP NAME' }}
      </h2>
      <p class="business-address">{{ settings.address || 'Address: Lorem Ipsum, 23-10' }}</p>
      <p v-if="settings.phone" class="business-phone">Telp. {{ settings.phone }}</p>
    </div>

    <!-- Separator -->
    <div class="separator"></div>

    <!-- Title -->
    <div class="title mt-1 mb-1 font-semibold">
      CASH RECEIPT
    </div>

    <div class="separator"></div>

    <!-- Customer Info -->
    <div class="mt-2 mb-1 leading-tight">
      <p>Date: {{ new Date().toLocaleString() }}</p>
      <p>Invoice #: INV-{{ Date.now().toString().slice(-6) }}</p>
      <p>Customer: {{ customer?.name || 'Walking Customer' }}</p>
      <p v-if="customer?.phone">Phone: {{ customer.phone }}</p>
    </div>

    <!-- Items -->
    <table class="w-full mt-2 mb-2">
      <thead>
        <tr>
          <th class="text-left">Description</th>
          <th class="text-right">Price</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td>{{ item.product.name }}</td>
          <td class="text-right">{{ formatCurrency(item.price * item.quantity) }}</td>
        </tr>
      </tbody>
    </table>

    <div class="separator"></div>

    <!-- Totals -->
    <div class="mt-2 mb-1">
      <div class="flex justify-between font-semibold text-[14px]">
        <span>Total</span>
        <span>{{ formatCurrency(total) }}</span>
      </div>
      <div class="flex justify-between">
        <span>Cash</span>
        <span>{{ formatCurrency(paid) }}</span>
      </div>
      <div class="flex justify-between">
        <span>Due</span>
        <span>{{ formatCurrency(due) }}</span>
      </div>
    </div>

    <div class="separator"></div>

    <!-- Card / Code Info -->
    <div class="text-[12px] leading-tight mt-1 flex justify-between">
        <p>Payment Method:</p>
        <p>{{ paymentMethod }}</p>
    </div>

    <div class="separator"></div>

    <!-- Footer -->
    <div class="footer text-center mt-3">
      <p class="font-semibold">THANK YOU!</p>
      <div class="barcode mt-2">||||||||||||||||||||||||||||</div>
      <p class="powered mt-2 text-[10px]">Powered by Infinity Flame Soft</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { formatCurrency } from '@/utils/helper';

const props = defineProps({
  items: Array,
  customer: Object,
  total: Number,
  paid: Number,
  due: Number,
  paymentMethod: String,
  storeInfo: Object,
});

const settings = props.storeInfo || {};
const visible = ref(false);

function show() {
  const printContent = document.querySelector('.receipt').innerHTML;
  const printWindow = window.open('', '', 'width=400,height=600');
  printWindow.document.write(`
    <html>
      <head>
        <title>Receipt</title>
        <style>
          body {
            font-family: monospace;
            width: 280px;
            margin: 0 auto;
            padding: 15px;
            background: #fff;
            color: #000;
            line-height: 1.3;
          }
          h2, p {
            text-align: center;
            margin: 0;
            padding: 0;
          }
          h2.business-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
          }
          p.business-address, p.business-phone {
            margin: 0;
          }
          .title {
            font-weight: 600;
            margin: 6px 0;
          }
          table {
            width: 100%;
            border-collapse: collapse;
          }
          th {
            font-weight: bold;
            border-bottom: 1px dashed #000;
            padding-bottom: 4px;
            text-align: left;
          }
          td {
            padding: 2px 0;
          }
          .text-right { text-align: right; }
          .flex { display: flex; justify-content: space-between; }
          .separator {
            text-align: center;
            margin: 6px 0;
            font-size: 12px;
            letter-spacing: 2px;
          }
          .separator::before {
            content: "********************************";
            display: block;
          }
          .barcode {
            font-weight: bold;
            letter-spacing: 2px;
            font-size: 14px;
            text-align: center;
          }
          .powered {
            font-size: 10px;
            text-align: center;
            margin-top: 4px;
          }
        </style>
      </head>
      <body>${printContent}</body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
}

defineExpose({ show });
</script>

<style scoped>
.receipt {
  width: 280px;
  background: white;
  color: #000;
  margin: 0 auto;
  padding: 14px;
  line-height: 1.3;
  text-align: center;
}

.header, .title, .footer {
  text-align: center;
}

.header h2,
.header p {
  text-align: center;
}

.separator {
  text-align: center;
  margin: 6px 0;
  font-size: 12px;
  letter-spacing: 2px;
}

.separator::before {
  content: "********************************";
  display: block;
  color: #000;
}

.barcode {
  font-weight: bold;
  letter-spacing: 2px;
  font-size: 14px;
}

.powered {
  font-size: 10px;
  margin-top: 4px;
}
</style>
