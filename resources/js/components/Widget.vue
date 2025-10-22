<script setup>
const props = defineProps({
  title: { type: String, default: "" },
  value: { type: [String, Number], default: "" },
  gradientFrom: { type: String, default: "from-blue-500" },
  gradientTo: { type: String, default: "to-indigo-400" },
});
</script>

<template>
  <div
    class="relative rounded-xl p-4 flex flex-col items-center justify-center text-white shadow-lg min-h-[100px]"
    :class="`bg-gradient-to-r ${gradientFrom} ${gradientTo}`"
  >
    <slot name="icon" />
    <h3 class="mt-2 text-center font-semibold">{{ title }}</h3>
    <p v-if="value" class="mt-1 text-xl font-bold"> <span class="text-2xl font-bold">৳</span>{{ value }}</p>
  </div>
</template>
