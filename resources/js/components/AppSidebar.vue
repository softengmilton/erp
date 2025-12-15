<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid,Store,ShoppingCart,ShoppingBasket,Warehouse,Landmark,ClipboardMinus,Settings} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid,
  },
  {
    title: 'Store',
    icon: Store,
    children: [
      {
        title: 'Orders',
        icon: ShoppingCart,
        subchildren: [
          {
            title: 'Orders',
            href: '/store/orders',
          },
          {
            title: 'POS',
            href: '/store/pos',
          },
        ],
      },
      {
        title: 'Products',
        icon: ShoppingBasket,
        subchildren: [
          {
            title: 'Types',
            href: '/store/product-types',
          },
          {
            title: 'Products',
            href: '/store/products',
          },
        ],
      },
      {
        title: 'Stocks',
        icon: Warehouse,
        subchildren: [
          {
            title: 'Stocks',
            href: '/store/stocks',
          },
          {
            title: 'Movement Logs',
            href: '/store/stock-lists',
          },
        ],
      },
      {
        title: 'Expenses',
        icon: Landmark,
        subchildren: [
          {
            title: 'Types',
            href: '/store/expense-types',
          },
          {
            title: 'Expenses',
            href: '/store/expenses',
          },
        ],
      },
      {
        title: 'Reports',
        icon: ClipboardMinus,
        subchildren: [
          {
            title: 'Reports',
            href: '/store/reports',
          },
          {
            title: 'Products Reports',
            href: '/store/products-reports',
          },
          {
            title: 'Stock Reports',
            href: '/store/stock-reports',
          },
          {
            title: 'Stock Product Reports',
            href: '/store/stock-product-reports',
          },
        //   {
        //   title: 'Assets',
        //   href: '/store/total-asset',
        //   },
        ],
      },
      {
        title: 'Settings',
        href: '/store/settings',
        icon: Settings,
      },
    ],
  },
  {
    title: 'Resturent',
    href: '/hr',
    icon: Folder,
  },
];


const footerNavItems: NavItem[] = [
    {
        title: 'Infinity Flame Soft',
        href: 'https://infinityflamesoft.com/',
        icon: BookOpen,
    },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#vue',
    //     icon: BookOpen,
    // },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
