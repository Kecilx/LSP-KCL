<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { 
    Sidebar, 
    SidebarContent, 
    SidebarFooter, 
    SidebarHeader, 
    SidebarMenu, 
    SidebarMenuButton, 
    SidebarMenuItem,
    SidebarGroup,
    SidebarGroupLabel
} from '@/components/ui/sidebar';
import { Link } from '@inertiajs/vue3';
import { Folder, LayoutGrid, ShoppingCart, Store, FileText } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

// Section 1: Detail/Utama
const detailNavItems: any[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        url: route('dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Transaksi',
        href: route('transactions.index'),
        url: route('transactions.index'),
        icon: Store,
    },
    {
        title: 'History Transaksi',
        href: route('transactions.history'),
        url: route('transactions.history'),
        icon: FileText,
    }
];

// Section 2: Master Data
const masterNavItems: any[] = [
    {
        title: 'Master Kategori',
        href: route('categories.index'),
        url: route('categories.index'),
        icon: Folder,
    },
    {
        title: 'Master Item',
        href: route('items.index'),
        url: route('items.index'),
        icon: ShoppingCart,
    }
];

const footerNavItems: any[] = [];
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
            <!-- Section Detail -->
            <SidebarGroup>
                <SidebarGroupLabel>RINGKASAN</SidebarGroupLabel>
                <NavMain :items="detailNavItems" />
            </SidebarGroup>

            <!-- Section Master -->
            <SidebarGroup>
                <SidebarGroupLabel>MASTER DATA</SidebarGroupLabel>
                <NavMain :items="masterNavItems" />
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>