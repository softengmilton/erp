<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
  settings: Object,
});

console.log(props.settings);

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

    form.logo = file;      // attach file to form
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
    console.log(form.data()); // only user inputs
    form.post("/store/settings", {
      preserveScroll: true,
      onSuccess: () => {
        console.log("Settings saved successfully!");
      },
    });
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
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
      <!-- Card -->
      <div class="w-full bg-white rounded-2xl shadow-lg p-[50px]">
        <!-- Title -->
        <h2 class="text-2xl font-semibold text-gray-800 mb-1">Business Settings</h2>
        <p class="text-gray-500 mb-8">Configure your shop’s general information and preferences.</p>

        <!-- Logo Upload -->
        <div class="mb-8">
          <label class="block text-sm font-medium text-gray-700 mb-2">Business Logo</label>
          <div
            class="border-2 border-dashed border-blue-200 rounded-xl p-6 flex flex-col items-center justify-center text-center hover:border-blue-400 transition"
          >
            <div v-if="imagePreview" class="mb-3">
              <img :src="imagePreview" alt="Logo Preview" class="w-32 h-32 object-contain rounded-lg border" />
              <button
                type="button"
                @click="removeImage"
                class="mt-2 px-4 py-1 text-sm text-red-600 border border-red-400 rounded hover:bg-red-50"
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
              <label for="logoUpload" class="text-blue-600 cursor-pointer underline">choose a file</label>
              to upload
            </p>
            <input
              id="logoUpload"
              type="file"
              accept="image/png"
              class="hidden"
              @change="handleFileUpload"
            />
            <p class="text-xs text-gray-400 mt-1">Upload a PNG file (max 50KB). This logo will appear on invoices and reports.</p>
          </div>
        </div>

        <!-- Settings Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <!-- Business Title -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Business Title</label>
            <input
              type="text"
              v-model="form.business_title"
              placeholder="e.g. Jannat Park Store"
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Business Email -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Business Email</label>
            <input
              type="email"
              v-model="form.business_email"
              placeholder="info@yourstore.com"
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Phone Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
            <input
              type="tel"
              v-model="form.phone"
              placeholder="+880 1785 283 596"
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <input
              type="text"
              v-model="form.address"
              placeholder="58/C, Neharipara, Akhalia, Sylhet"
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Description -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Business Description</label>
            <textarea
              rows="3"
              v-model="form.description"
              placeholder="Write a short description about your business..."
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>

          <!-- Currency -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
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

          <!-- Opening Time -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Opening Time</label>
            <input
              type="time"
              v-model="form.opening_time"
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Closing Time -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Closing Time</label>
            <input
              type="time"
              v-model="form.closing_time"
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Invoice Footer Text -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Footer Text</label>
            <textarea
              rows="2"
              v-model="form.invoice_footer_text"
              placeholder="e.g. Thank you for shopping with us!"
              class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex items-center justify-end gap-3">
          <button
            type="button"
            @click="cancel"
            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="submitForm"
            class="px-6 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
