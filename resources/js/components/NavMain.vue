<script setup lang="ts">
import { ref } from 'vue';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuItem,
  SidebarMenuButton,
} from '@/components/ui/sidebar';

defineProps<{ items: NavItem[] }>();

const page = usePage();
const isActive = (href: string) => page.url.startsWith(href);

// Track expanded items by title
const expanded = ref<Record<string, boolean>>({});

const toggle = (title: string) => {
  expanded.value[title] = !expanded.value[title];
};

const isExpanded = (title: string) => expanded.value[title];
</script>

<template>
  <SidebarGroup class="px-2 py-0">
    <SidebarGroupLabel>Platform</SidebarGroupLabel>
    <SidebarMenu>
      <template v-for="item in items" :key="item.title">
        <SidebarMenuItem>
          <SidebarMenuButton
            as-child
            :is-active="isActive(item.href)"
            :tooltip="item.title"
            @click="item.children ? toggle(item.title) : null"
          >
            <Link :href="item.href">
              <component :is="item.icon" />
              <span>{{ item.title }}</span>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>

        <!-- Collapsible children -->
        <transition name="slide" mode="out-in">
          <SidebarMenu
            v-if="item.children && isExpanded(item.title)"
            class="pl-4"
          >
            <template v-for="child in item.children" :key="child.title">
              <SidebarMenuItem>
                <SidebarMenuButton
                  as-child
                  :is-active="isActive(child.href)"
                  :tooltip="child.title"
                  @click="child.subchildren ? toggle(child.title) : null"
                >
                  <Link :href="child.href">
                    <component :is="child.icon" />
                    <span>{{ child.title }}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>

              <!-- Subchildren (nested under children) -->
              <transition name="fade">
                <SidebarMenu
                  v-if="child.subchildren && isExpanded(child.title)"
                  class="pl-4"
                >
                  <SidebarMenuItem
                    v-for="sub in child.subchildren"
                    :key="sub.title"
                  >
                    <SidebarMenuButton
                      as-child
                      :is-active="isActive(sub.href)"
                      :tooltip="sub.title"
                    >
                      <Link :href="sub.href">
                        <span>• {{ sub.title }}</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                </SidebarMenu>
              </transition>
            </template>
          </SidebarMenu>
        </transition>
      </template>
    </SidebarMenu>
  </SidebarGroup>
</template>

