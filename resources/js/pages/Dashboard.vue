<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Menangkap data 'stats' yang dikirim dari DashboardController
defineProps<{
    name?: string;
    stats?: {
        categories: number;
        items: number;
        transactions: number;
        income_today: number;
    };
}>();

// Fungsi untuk memformat angka menjadi format Rupiah
const formatRupiah = (angka: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            
            <!-- Grid untuk Kartu Statistik (4 Kolom) -->
            <div class="grid auto-rows-min gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                
                <!-- Kartu Total Kategori -->
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800/40 p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Kategori</div>
                    <div class="mt-4 text-4xl font-bold text-gray-800 dark:text-white">{{ stats?.categories || 0 }}</div>
                </div>

                <!-- Kartu Total Item -->
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800/40 p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Item Produk</div>
                    <div class="mt-4 text-4xl font-bold text-gray-800 dark:text-white">{{ stats?.items || 0 }}</div>
                </div>

                <!-- Kartu Total Transaksi -->
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800/40 p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaksi Hari Ini</div>
                    <div class="mt-4 text-4xl font-bold text-gray-800 dark:text-white">{{ stats?.transactions || 0 }}</div>
                </div>

                <!-- Kartu Pendapatan -->
                <div class="relative overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800/40 p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pendapatan Hari Ini</div>
                    <div class="mt-4 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ formatRupiah(stats?.income_today || 0) }}</div>
                </div>

            </div>

            <!-- Pesan Selamat Datang -->
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800/40 p-6 shadow-sm md:min-h-min">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-3">Selamat Datang!</h3>
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                    Sistem Point of Sales (Aplikasi Kasir) siap digunakan. Silakan gunakan menu navigasi di samping untuk mengelola Master Kategori, Master Item, dan melakukan Transaksi.
                </p>
            </div>
            
        </div>
    </AppLayout>
</template>