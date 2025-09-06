<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import Widget from "@/components/Widget.vue";

const props = defineProps({
  settings: Object,
});

// Form
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

// Image preview refs
const imagePreview = ref(props.settings.logo_url || null);
const imageFile = ref(null);

// ✅ Handle file upload
function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  form.logo = file; // attach file to form
  imageFile.value = file;

  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.value = e.target.result;
  };
  reader.readAsDataURL(file);
}

// ✅ Remove image
function removeImage() {
  form.logo = null;
  imageFile.value = null;
  imagePreview.value = null;

  const fileInput = document.getElementById("logoUpload");
  if (fileInput) fileInput.value = "";
}

// ✅ Cancel (reset form)
function cancel() {
  form.reset();
  removeImage();
}

// ✅ Submit form
function submitForm() {
  form.post("/store/settings", {
    preserveScroll: true,
    onSuccess: () => {
      console.log("Settings saved successfully!");
    },
  });
}

// Cash withdrawal data
const cashData = ref({
  currentCash: 45870,
  bkashBalance: 12500,
  totalWithdrawn: 125600,
  pendingWithdrawals: 8500,
});

const withdrawalMethods = ref([
  { id: "cash", name: "Cash", icon: "💰" },
  { id: "bkash", name: "bKash", icon: "📱" },
  { id: "bank", name: "Bank Transfer", icon: "🏦" },
]);

const withdrawalHistory = ref([
  {
    id: 1,
    amount: 12500,
    date: "2023-10-15",
    status: "Completed",
    method: "Bank Transfer",
  },
  { id: 2, amount: 8500, date: "2023-10-10", status: "Pending", method: "bKash" },
  { id: 3, amount: 15600, date: "2023-10-05", status: "Completed", method: "Cash" },
  { id: 4, amount: 9200, date: "2023-10-01", status: "Failed", method: "Bank Transfer" },
]);

// Withdrawal form
const withdrawalForm = ref({
  amount: "",
  method: "bkash",
  account: "",
});

function requestWithdrawal() {
  if (
    !withdrawalForm.value.amount ||
    withdrawalForm.value.amount > cashData.value.currentCash
  ) {
    alert("Invalid withdrawal amount");
    return;
  }

  console.log("Requesting withdrawal:", withdrawalForm.value);
  // Here you would typically send an API request
  alert(`Withdrawal request for ৳${withdrawalForm.value.amount} submitted!`);
  withdrawalForm.value.amount = "";
  withdrawalForm.value.account = "";
}

// Breadcrumbs
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
              <!-- Logo Upload -->
              <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Business Logo</label
                >
                <div
                  class="border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center text-center hover:border-blue-400 transition-colors"
                >
                  <div v-if="imagePreview" class="mb-3">
                    <img
                      :src="imagePreview"
                      alt="Logo Preview"
                      class="w-32 h-32 object-contain rounded-lg border"
                    />
                    <button
                      type="button"
                      @click="removeImage"
                      class="mt-2 px-4 py-1 text-sm text-red-600 border border-red-400 rounded hover:bg-red-50 transition-colors"
                    >
                      Remove
                    </button>
                  </div>
                  <div v-else class="bg-blue-50 rounded-full p-4 mb-3">
                    <!-- Upload Icon -->
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-10 w-10 text-blue-500"
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
                  </div>
                  <p class="text-gray-600">
                    Drag & drop or
                    <label
                      for="logoUpload"
                      class="text-blue-600 cursor-pointer underline hover:text-blue-800"
                      >choose a file</label
                    >
                    to upload
                  </p>
                  <input
                    id="logoUpload"
                    type="file"
                    accept="image/png"
                    class="hidden"
                    @change="handleFileUpload"
                  />
                  <p class="text-xs text-gray-400 mt-1">
                    Upload a PNG file (max 50KB). This logo will appear on invoices and
                    reports.
                  </p>
                </div>
              </div>

              <!-- Settings Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Business Title -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Business Title</label
                  >
                  <input
                    type="text"
                    v-model="form.business_title"
                    placeholder="e.g. Jannat Park Store"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  />
                </div>

                <!-- Business Email -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Business Email</label
                  >
                  <input
                    type="email"
                    v-model="form.business_email"
                    placeholder="info@yourstore.com"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  />
                </div>

                <!-- Phone Number -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Phone Number</label
                  >
                  <input
                    type="tel"
                    v-model="form.phone"
                    placeholder="+880 1785 283 596"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  />
                </div>

                <!-- Address -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Address</label
                  >
                  <input
                    type="text"
                    v-model="form.address"
                    placeholder="58/C, Neharipara, Akhalia, Sylhet"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  />
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Business Description</label
                  >
                  <textarea
                    rows="3"
                    v-model="form.description"
                    placeholder="Write a short description about your business..."
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  ></textarea>
                </div>

                <!-- Currency -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Currency</label
                  >
                  <select
                    v-model="form.currency"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  >
                    <option value="BDT">BDT - Bangladeshi Taka</option>
                    <option value="USD">USD - US Dollar</option>
                    <option value="EUR">EUR - Euro</option>
                    <option value="INR">INR - Indian Rupee</option>
                  </select>
                </div>

                <!-- Opening Time -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Opening Time</label
                  >
                  <input
                    type="time"
                    v-model="form.opening_time"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  />
                </div>

                <!-- Closing Time -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Closing Time</label
                  >
                  <input
                    type="time"
                    v-model="form.closing_time"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  />
                </div>

                <!-- Invoice Footer Text -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Invoice Footer Text</label
                  >
                  <textarea
                    rows="2"
                    v-model="form.invoice_footer_text"
                    placeholder="e.g. Thank you for shopping with us!"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  ></textarea>
                </div>
              </div>

              <!-- Footer Buttons -->
              <div
                class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200"
              >
                <button
                  type="button"
                  @click="cancel"
                  class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  @click="submitForm"
                  class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors"
                >
                  Save
                </button>
              </div>
            </div>
          </div>

          <!-- Right Column - Cash Withdrawal Widgets -->
          <div class="lg:col-span-1 space-y-6">
            <!-- Cash Widgets -->
            <div class="grid grid-cols-2 gap-4">
              <Widget
                title="Current Cash"
                :value="`৳${cashData.currentCash.toLocaleString()}`"
                gradientFrom="from-green-400"
                gradientTo="to-teal-400"
              />
              <Widget
                title="bKash Balance"
                :value="`৳${cashData.bkashBalance.toLocaleString()}`"
                gradientFrom="from-blue-500"
                gradientTo="to-cyan-400"
              />
              <Widget
                title="Total"
                :value="`৳${cashData.totalWithdrawn.toLocaleString()}`"
                gradientFrom="from-purple-500"
                gradientTo="to-pink-400"
              />
              <Widget
                title="Withdrawals"
                :value="`৳${cashData.pendingWithdrawals.toLocaleString()}`"
                gradientFrom="from-yellow-500"
                gradientTo="to-orange-400"
              />
            </div>

            <!-- Withdrawal Request Form -->
            <div class="bg-white rounded-xl shadow-sm p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Request Withdrawal</h3>

              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Amount (৳)</label
                  >
                  <input
                    type="number"
                    v-model="withdrawalForm.amount"
                    :max="cashData.currentCash"
                    placeholder="Enter amount"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <p class="text-xs text-gray-500 mt-1">
                    Available: ৳{{ cashData.currentCash.toLocaleString() }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Method</label
                  >
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
                </div>

                <button
                  @click="requestWithdrawal"
                  class="w-full bg-green-600 text-white py-3 rounded-lg font-medium hover:bg-green-700 transition-colors"
                  :disabled="
                    !withdrawalForm.amount || withdrawalForm.amount > cashData.currentCash
                  "
                >
                  Request Withdrawal
                </button>
              </div>
            </div>

            <!-- Recent Withdrawals -->
            <div class="bg-white rounded-xl shadow-sm p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Withdrawals</h3>

              <div class="space-y-3">
                <div
                  v-for="withdrawal in withdrawalHistory"
                  :key="withdrawal.id"
                  class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-colors"
                >
                  <div class="flex justify-between items-start mb-2">
                    <span class="font-medium"
                      >৳{{ withdrawal.amount.toLocaleString() }}</span
                    >
                    <span
                      :class="{
                        'bg-green-100 text-green-800': withdrawal.status === 'Completed',
                        'bg-yellow-100 text-yellow-800': withdrawal.status === 'Pending',
                        'bg-red-100 text-red-800': withdrawal.status === 'Failed',
                      }"
                      class="px-2 py-1 rounded-full text-xs font-medium"
                    >
                      {{ withdrawal.status }}
                    </span>
                  </div>
                  <div class="flex justify-between text-sm text-gray-600">
                    <span>{{ withdrawal.method }}</span>
                    <span>{{ withdrawal.date }}</span>
                  </div>
                </div>
              </div>

              <button
                class="w-full mt-4 text-blue-600 hover:text-blue-800 font-medium text-sm"
              >
                View All Transactions →
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Smooth transitions for all interactive elements */
input,
select,
textarea,
button {
  transition: all 0.2s ease;
}

/* Custom focus styles */
input:focus,
select:focus,
textarea:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Disabled button styles */
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
