<template>
  <div class="stock-receipt font-mono text-[13px]" v-show="visible">
    <!-- Header -->
    <div class="header mb-3">
      <h2 class="business-title">{{ settings.business_title || 'STORE NAME' }}</h2>
      <p class="business-address">{{ settings.address || 'Address: Lorem Ipsum 23-10' }}</p>
      <p v-if="settings.phone" class="business-phone">Telp. {{ settings.phone }}</p>
    </div>

    <div class="separator"></div>

    <!-- Title -->
    <div class="title mt-1 mb-1 font-semibold">
      PURCHASE INVOICE
    </div>

    <div class="separator"></div>

    <!-- Supplier Info -->
    <div class="mt-2 mb-1 leading-tight">
      <p><strong>Date:</strong> {{ new Date().toLocaleString() }}</p>
      <p><strong>Invoice #:</strong> {{ invoiceNumber }}</p>
      <p><strong>Supplier:</strong> {{ supplierName || 'N/A' }}</p>
    </div>

    <!-- Items Table -->
    <table class="w-full mt-2 mb-2 table-bordered">
      <thead>
        <tr>
          <th>Item</th>
          <th>Qty</th>
          <th>Unit</th>
          <th>Landed</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, i) in items" :key="i">
          <td>{{ item.name }}</td>
          <td class="text-right">{{ item.quantity }}</td>
          <td class="text-right">{{ formatCurrency(item.unit_cost) }}</td>
          <td class="text-right">{{ formatCurrency(item.unit_landed_cost) }}</td>
          <td class="text-right">{{ formatCurrency(item.total_cost) }}</td>
        </tr>
      </tbody>
    </table>

    <div class="separator"></div>

    <!-- Totals -->
    <div class="mt-2 mb-1">
      <div class="flex justify-between text-[13px]">
        <span>Subtotal</span>
        <span>{{ formatCurrency(subtotal) }}</span>
      </div>
      <div class="flex justify-between text-[13px]">
        <span>Shipping</span>
        <span>{{ formatCurrency(shipping) }}</span>
      </div>
      <div class="flex justify-between text-[13px]">
        <span>Other Fees</span>
        <span>{{ formatCurrency(otherFees) }}</span>
      </div>
      <div class="flex justify-between font-semibold text-[14px] mt-1">
        <span>Total</span>
        <span>{{ formatCurrency(total) }}</span>
      </div>
    </div>

    <div class="separator"></div>

    <!-- Notes -->
    <div class="text-[12px] leading-tight mt-1" v-if="note">
      <p><strong>Note:</strong> {{ note }}</p>
    </div>

    <div class="separator"></div>

    <!-- Footer -->
    <div class="footer text-center mt-3">
      <p class="font-semibold">THANK YOU FOR YOUR PURCHASE!</p>
      <div class="barcode mt-2">||||||||||||||||||||||||||||</div>
      <p class="powered mt-2 text-[10px]">Powered by Infinity Flame Soft</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { formatCurrency } from "@/utils/helper";

const props = defineProps({
  items: { type: Array, default: () => [] },
  supplierName: String,
  invoiceNumber: String,
  subtotal: Number,
  shipping: Number,
  otherFees: Number,
  total: Number,
  note: String,
  storeInfo: { type: Object, default: () => ({}) },
});

const settings = props.storeInfo || {};
const visible = ref(false);

function show() {
  const printContent = document.querySelector('.stock-receipt').innerHTML;
  const printWindow = window.open('', '', 'width=400,height=600');
  printWindow.document.write(`
    <html>
      <head>
        <title>Receipt</title>
        <style>
          body { font-family: monospace; width: 280px; margin: 0 auto; padding: 15px; color: #000; line-height: 1.3; }
          h2, p { text-align: center; margin: 0; padding: 0; }
          h2.business-title { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
          table { width: 100%; border-collapse: collapse; margin-top: 4px; }
          th, td { border: 1px solid #000; padding: 4px 2px; font-size: 12px; }
          th { font-weight: bold; text-align: left; }
          td.text-right { text-align: right; }
          .flex { display: flex; justify-content: space-between; }
          .separator::before { content: "********************************"; display: block; margin: 6px 0; font-size: 12px; letter-spacing: 2px; }
          .barcode { font-weight: bold; letter-spacing: 2px; font-size: 14px; text-align: center; margin-top: 4px; }
          .powered { font-size: 10px; text-align: center; margin-top: 4px; }
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
.stock-receipt {
  width: 280px;
  background: white;
  color: #000;
  margin: 0 auto;
  padding: 14px;
  line-height: 1.3;
  font-family: monospace;
}

.header, .title, .footer {
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
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 4px;
}

table th, table td {
  border: 1px solid #000;
  padding: 4px 2px;
  font-size: 12px;
}

td.text-right {
  text-align: right;
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
