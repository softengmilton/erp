<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import Toaster from '../components/Toaster.vue'
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'
interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
const page = usePage();

watch(
  () => page.props.toast,
  (toast) => {
    if (toast) {
      window.dispatchEvent(new CustomEvent('toast', { detail: toast }))
    }
  },
  { immediate: true }
)

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
          <Toaster />
    </AppLayout>
</template>
