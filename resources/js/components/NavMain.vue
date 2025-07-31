<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight } from 'lucide-vue-next';
import type { NavItem } from '@/types';

defineProps<{
  items: NavItem[];
}>();

const openMenus = ref<Record<string, boolean>>({});
const page = usePage();

function doesPathMatch(item: NavItem, currentPath: string): boolean {
  if (item.href && currentPath.startsWith(item.href)) {
    return true;
  }
  if (item.children) {
    return item.children.some(child => doesPathMatch(child, currentPath));
  }
  if ('subchildren' in item && item.subchildren) {
    return item.subchildren.some(sub => doesPathMatch(sub, currentPath));
  }
  return false;
}

/**
 * Open menus that match the current URL path.
 */
function openMenusByUrl(items: NavItem[], currentPath: string) {
  items.forEach(item => {
    if (doesPathMatch(item, currentPath)) {
      openMenus.value[item.title] = true;

      // Also open children if they match
      if (item.children) {
        item.children.forEach(child => {
          if (doesPathMatch(child, currentPath)) {
            openMenus.value[child.title] = true;
          }
        });
      }
    }
  });
}

function toggleMenu(title: string) {
  openMenus.value[title] = !openMenus.value[title];
}

onMounted(() => {
  const currentPath = page.url;
  openMenusByUrl(__props.items, currentPath);
});
</script>

<template>
  <ul class="space-y-1">
    <li v-for="item in items" :key="item.title" class="text-sm">
      <div v-if="item.children" class="flex flex-col">
        <button
          @click="toggleMenu(item.title)"
          class="flex items-center justify-between w-full px-3 py-2 hover:bg-gray-100 rounded dark:hover:text-gray-900"
        >
          <div class="flex items-center gap-2">
            <component :is="item.icon" class="w-4 h-4" />
            <span>{{ item.title }}</span>
          </div>
          <component
            :is="openMenus[item.title] ? ChevronDown : ChevronRight"
            class="w-4 h-4"
          />
        </button>
        <ul v-if="openMenus[item.title]" class="pl-6 mt-1 space-y-1">
          <li v-for="child in item.children" :key="child.title">
            <div v-if="child.subchildren">
              <button
                @click="toggleMenu(child.title)"
                class="flex items-center justify-between w-full px-3 py-2 hover:bg-gray-50 rounded dark:hover:text-gray-900"
              >
                <div class="flex items-center gap-2">
                  <component :is="child.icon" class="w-4 h-4" />
                  <span>{{ child.title }}</span>
                </div>
                <component
                  :is="openMenus[child.title] ? ChevronDown : ChevronRight"
                  class="w-4 h-4"
                />
              </button>
              <ul
                v-if="openMenus[child.title]"
                class="pl-6 mt-1 space-y-1"
              >
                <li v-for="sub in child.subchildren" :key="sub.title">
                  <Link
                    :href="sub.href"
                    class="flex items-center gap-2 px-3 py-1 hover:bg-gray-100 rounded text-sm dark:hover:text-gray-900"
                  >
                    <span>{{ sub.title }}</span>
                  </Link>
                </li>
              </ul>
            </div>
            <div v-else>
              <Link
                :href="child.href"
                class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 rounded text-sm  dark:hover:text-gray-900"
              >
                <component :is="child.icon" class="w-4 h-4" />
                <span>{{ child.title }}</span>
              </Link>
            </div>
          </li>
        </ul>
      </div>
      <div v-else>
        <Link
          :href="item.href"
          class="flex items-center gap-2 px-3 py-2 hover:bg-gray-100 rounded  dark:hover:text-gray-900"
        >
          <component :is="item.icon" class="w-4 h-4" />
          <span>{{ item.title }}</span>
        </Link>
      </div>
    </li>
  </ul>
</template>
