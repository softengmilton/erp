<template>
  <div
    v-if="visible"
    class="fixed top-5 right-5 z-50 px-4 py-3 rounded-lg shadow text-white transition-all duration-300"
    :class="toastClass"
  >
    {{ toast.message }}
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const toast = ref({})
const visible = ref(false)

const toastClass = computed(() => {
  if (toast.value.type === 'success') return 'bg-green-500'
  if (toast.value.type === 'error') return 'bg-red-500'
  return 'bg-gray-700'
})

onMounted(() => {
  window.addEventListener('toast', (e) => {
    toast.value = e.detail
    visible.value = true
    setTimeout(() => {
      visible.value = false
    }, 3000)
  })
})
</script>
