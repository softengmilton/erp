<template>
  <div
    v-if="visible"
    class="fixed top-5 right-5 z-50 flex items-center pl-12 pr-4 py-5 rounded-lg shadow-lg text-white transition-all duration-300 max-w-xs overflow-visible"
    :class="toastClass"
  >
    <div class="relative w-full">
      <!-- Toast Icon -->
      <img
        :src="toastImage"
        alt="icon"
        class="absolute -left-16 -top-8 w-16 h-16 object-contain z-10"
      />

      <!-- Close Button -->
      <button
        @click="closeToast"
        class="absolute top-0 right-0 text-white text-lg font-bold px-2 z-20 hover:text-gray-300"
      >
        ×
      </button>

      <!-- Message -->
      <p class="text-sm font-medium z-10 pr-6">
        {{ toast.message }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";

const toast = ref({});
const visible = ref(false);

const toastClass = computed(() => {
  switch (toast.value.type) {
    case "success":
      return "bg-green-600";
    case "error":
      return "bg-red-600";
    case "info":
      return "bg-blue-500";
    case "warning":
      return "bg-yellow-500 text-black";
    default:
      return "bg-gray-700";
  }
});

const toastImage = computed(() => {
  switch (toast.value.type) {
    case "success":
      return "/assets/img/alert/success.png";
    case "error":
      return "/assets/img/alert/error.png";
    case "info":
      return "/assets/img/alert/info.png";
    case "warning":
      return "/assets/img/alert/warning.png";
    default:
      return "/assets/img/alert/info.png";
  }
});

const closeToast = () => {
  visible.value = false;
  localStorage.removeItem("toast");
};

window.addEventListener("toast", (e) => {
  localStorage.setItem("toast", JSON.stringify(e.detail));
  toast.value = e.detail;
  visible.value = true;
    //  setTimeout(() => {
    //   closeToast();
    // }, 3000);
});

onMounted(() => {
  const savedToast = localStorage.getItem("toast");
  if (savedToast) {
    toast.value = JSON.parse(savedToast);
    visible.value = true;
    //  setTimeout(() => {
    //   closeToast();
    // }, 3000);
  }
});
</script>
