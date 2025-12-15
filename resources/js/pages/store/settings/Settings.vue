<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Widget from "@/components/Widget.vue";
import { formatNumber } from "@/utils/helper";

const props = defineProps({
  settings: Object,
  financials: Object,
  withdrawals: Array,
});

/**
 * Business Settings Form
 */
const form = useForm({
  business_title: props.settings.business_title || "",
  business_email: props.settings.business_email || "",
  phone: props.settings.phone || "",
  address: props.settings.address || "",
  description: props.settings.description || "",
  currency: props.settings.currency || "BDT",
  opening_time: props.settings.opening_time || "",
  closing_time: props.settings.closing_time || "",
  invoice_footer_text: props.settings.invoice_footer_text || "",
  logo: null,
});

const imagePreview = ref(props.settings.logo_url || null);
const imageFile = ref(null);

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;
  form.logo = file;
  imageFile.value = file;

  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
}

function removeImage() {
  form.logo = null;
  imageFile.value = null;
  imagePreview.value = null;
  const fileInput = document.getElementById("logoUpload");
  if (fileInput) fileInput.value = "";
}

function cancel() {
  form.reset();
  removeImage();
}

/**
 * Submit the business settings form
 */
function submitForm() {
  form.post("/store/settings", {
    preserveScroll: true,
    onSuccess: () => {
      console.log("Settings saved successfully!");
    },
  });
}

const withdrawalForm = useForm({
  amount: "",
  method: "bkash",
  account: "",
});

const withdrawalMethods = ref([
  { id: "cash", name: "Cash", icon: "💰" },
  { id: "bkash", name: "bKash", icon: "📱" },
  { id: "bank", name: "Bank Transfer", icon: "🏦" },
]);

/**
 * Request a withdrawal
 */
function requestWithdrawal() {
  if (
    !withdrawalForm.amount ||
    withdrawalForm.amount > props.financials.availableBalance
  ) {
    alert("Invalid withdrawal amount");
    return;
  }
  withdrawalForm.post("/withdrawals/store", {
    preserveScroll: true,
    onSuccess: () => {
      alert("Withdrawal request submitted successfully!");
      withdrawalForm.reset();
    },
    onError: (errors) => {
      console.error("Validation errors:", errors);
    },
  });
}

const breadcrumbs = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Settings", href: "/store/settings" },
];
</script>

<template>
  <Head title="Business Profile" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="px-4 sm:px-6 lg:px-8">
        <!-- Title -->
        <div class="mb-8">
          <h2 class="text-2xl font-semibold text-gray-800">Business Settings</h2>
          <p class="text-gray-500 mt-1">
            Configure your shop's general information and preferences.
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left Column - Settings Form -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-6">
              <!-- ✅ Logo Upload -->
              <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Business Logo
                </label>
                <div
                  class="border-2 border-dashed border-gray-300 rounded-xl p-0 flex flex-col items-center justify-center text-center hover:border-blue-400 transition-colors relative w-full max-w-7xl mx-auto"
                >
                  <!-- Image Preview -->
                  <div
                    v-if="imagePreview"
                    class="w-full h-64 relative overflow-hidden rounded-xl cursor-pointer"
                    @click="$refs.logoUpload.click()"
                  >
                    <img
                      :src="imagePreview"
                      alt="Logo Preview"
                      class="w-full h-full object-cover"
                    />
                    <button
                      type="button"
                      @click.stop="removeImage"
                      class="absolute top-2 right-2 w-6 h-6 flex items-center justify-center text-white bg-red-600 rounded-full hover:bg-red-700 transition-colors"
                    >
                      ✕
                    </button>
                  </div>

                  <!-- Placeholder / Upload Icon -->
                  <div
                    v-else
                    class="w-full h-64 flex flex-col items-center justify-center bg-blue-50 rounded-xl cursor-pointer"
                    @click="$refs.logoUpload.click()"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-12 w-12 text-blue-500 mb-2"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4-4m0 0l-4 4m4-4v12"
                      />
                    </svg>
                    <p class="text-gray-600">
                      Drag & drop or
                      <span class="text-blue-600 underline hover:text-blue-800"
                        >choose a file</span
                      >
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                      Upload a PNG file (max 50KB). This logo will appear on invoices and
                      reports.
                    </p>
                  </div>

                  <!-- Hidden File Input -->
                  <input
                    ref="logoUpload"
                    type="file"
                    accept="image/png"
                    class="hidden"
                    @change="handleFileUpload"
                  />
                </div>
              </div>

              <!-- ✅ Settings Form Fields -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Business Title
                  </label>
                  <input
                    type="text"
                    v-model="form.business_title"
                    placeholder="e.g. Jannat Park Store"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Business Email
                  </label>
                  <input
                    type="email"
                    v-model="form.business_email"
                    placeholder="info@yourstore.com"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Phone Number
                  </label>
                  <input
                    type="tel"
                    v-model="form.phone"
                    placeholder="+880 1785 283 596"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Address
                  </label>
                  <input
                    type="text"
                    v-model="form.address"
                    placeholder="58/C, Neharipara, Akhalia, Sylhet"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Business Description
                  </label>
                  <textarea
                    rows="3"
                    v-model="form.description"
                    placeholder="Write a short description about your business..."
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Currency
                  </label>
                  <select
                    v-model="form.currency"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="BDT">BDT - Bangladeshi Taka</option>
                    <option value="USD">USD - US Dollar</option>
                    <option value="EUR">EUR - Euro</option>
                    <option value="INR">INR - Indian Rupee</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Opening Time
                  </label>
                  <input
                    type="time"
                    v-model="form.opening_time"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Closing Time
                  </label>
                  <input
                    type="time"
                    v-model="form.closing_time"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Invoice Footer Text
                  </label>
                  <textarea
                    rows="2"
                    v-model="form.invoice_footer_text"
                    placeholder="e.g. Thank you for shopping with us!"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  ></textarea>
                </div>
              </div>

              <!-- ✅ Footer Buttons -->
              <div
                class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200"
              >
                <button
                  type="button"
                  @click="cancel"
                  class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  @click="submitForm"
                  class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700"
                >
                  Save
                </button>
              </div>
            </div>
          </div>

          <!-- ✅ Right Column - Financial Widgets -->
          <div class="lg:col-span-1 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
              <!-- Total overview first -->
              <Widget
                title="Total Balance"
                :value="`${formatNumber(props.financials.totalBalance)}`"
                gradientFrom="from-purple-500"
                gradientTo="to-pink-400"
              />

              <!-- What is usable right now -->
              <Widget
                title="Available Balance"
                :value="`${formatNumber(props.financials.availableBalance)}`"
                gradientFrom="from-pink-500"
                gradientTo="to-rose-400"
              />

              <!-- Breakdowns -->
              <Widget
                title="Current Cash"
                :value="`${formatNumber(props.financials.currentCash)}`"
                gradientFrom="from-green-400"
                gradientTo="to-teal-400"
              />
              <Widget
                title="bKash Balance"
                :value="`${formatNumber(props.financials.bkashBalance)}`"
                gradientFrom="from-blue-500"
                gradientTo="to-cyan-400"
              />

              <!-- Outflow / obligations -->
              <Widget
                title="Withdrawn"
                :value="`${formatNumber(props.financials.totalWithdrawn)}`"
                gradientFrom="from-yellow-500"
                gradientTo="to-orange-400"
              />
              <Widget
                title="Due Balance"
                :value="`${formatNumber(props.financials.dueBalance)}`"
                gradientFrom="from-red-500"
                gradientTo="to-pink-500"
              />
            </div>

            <!-- ✅ Withdrawal Request -->
            <div class="bg-white rounded-xl shadow-sm p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Request Withdrawal</h3>

              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Amount (৳)
                  </label>
                  <input
                    type="number"
                    v-model="withdrawalForm.amount"
                    :max="props.financials.availableBalance"
                    placeholder="Enter amount"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p class="text-xs text-gray-500 mt-1">
                    Available: ৳{{ props.financials.availableBalance.toLocaleString() }}
                  </p>
                  <p
                    v-if="withdrawalForm.errors.amount"
                    class="text-red-600 text-sm mt-1"
                  >
                    {{ withdrawalForm.errors.amount }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Method
                  </label>
                  <select
                    v-model="withdrawalForm.method"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option
                      v-for="method in withdrawalMethods"
                      :key="method.id"
                      :value="method.id"
                    >
                      {{ method.icon }} {{ method.name }}
                    </option>
                  </select>
                  <p
                    v-if="withdrawalForm.errors.method"
                    class="text-red-600 text-sm mt-1"
                  >
                    {{ withdrawalForm.errors.method }}
                  </p>
                </div>

                <div v-if="withdrawalForm.method !== 'cash'">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{
                      withdrawalForm.method === "bkash"
                        ? "bKash Number"
                        : "Account Number"
                    }}
                  </label>
                  <input
                    type="text"
                    v-model="withdrawalForm.account"
                    :placeholder="
                      withdrawalForm.method === 'bkash' ? '01XXXXXXXXX' : 'Account number'
                    "
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p
                    v-if="withdrawalForm.errors.account"
                    class="text-red-600 text-sm mt-1"
                  >
                    {{ withdrawalForm.errors.account }}
                  </p>
                </div>

                <button
                  @click="requestWithdrawal"
                  class="w-full bg-green-600 text-white py-3 rounded-lg font-medium hover:bg-green-700"
                  :disabled="
                    !withdrawalForm.amount ||
                    withdrawalForm.amount > props.financials.availableBalance ||
                    withdrawalForm.processing
                  "
                >
                  <span v-if="withdrawalForm.processing">Submitting...</span>
                  <span v-else>Request Withdrawal</span>
                </button>
              </div>
            </div>

            <!-- ✅ Withdrawal Transactions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">
                Withdrawal Transactions
              </h3>

              <div v-if="props.withdrawals.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm text-left border border-gray-200 rounded-lg">
                  <thead class="bg-gray-100 text-gray-700">
                    <tr>
                      <th class="px-4 py-2">Date</th>
                      <th class="px-4 py-2">Amount</th>
                      <th class="px-4 py-2">Method</th>
                      <th class="px-4 py-2">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="w in props.withdrawals" :key="w.id" class="border-t">
                      <td class="px-4 py-2">
                        {{ new Date(w.created_at).toLocaleDateString() }}
                      </td>
                      <td class="px-4 py-2">৳{{ Number(w.amount).toLocaleString() }}</td>
                      <td class="px-4 py-2 capitalize">{{ w.method }}</td>
                      <td class="px-4 py-2">
                        <span
                          :class="{
                            'text-yellow-600 bg-yellow-100 px-2 py-1 rounded text-xs':
                              w.status === 'pending',
                            'text-green-600 bg-green-100 px-2 py-1 rounded text-xs':
                              w.status === 'approved',
                            'text-red-600 bg-red-100 px-2 py-1 rounded text-xs':
                              w.status === 'rejected',
                          }"
                        >
                          {{ w.status }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <p v-else class="text-gray-500 text-sm">No withdrawals yet.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
input,
select,
textarea,
button {
  transition: all 0.2s ease;
}
input:focus,
select:focus,
textarea:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
