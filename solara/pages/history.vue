<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

// Setup Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Riwayat Transaksi',
        href: '/history',
    },
];

// Interface TypeScript
interface User {
    id: number;
    name: string;
}

interface Item {
    id: number;
    name: string;
}

interface TransactionDetail {
    id: number;
    quantity: number;
    qty: number;
    price: number;
    subtotal: number;
    item: Item;
}

interface Transaction {
    id: number;
    date: string;
    total: number;
    paid: number;
    change: number;
    user: User;
    details?: TransactionDetail[];
}

// Props dari TransactionHistoryController
const props = defineProps<{
    transactions: Transaction[];
}>();

// --- STATE ---
const searchQuery = ref('');
const isModalOpen = ref(false);
const selectedTransaction = ref<Transaction | null>(null);
const isLoadingDetails = ref(false);

// --- UTILS ---
const formatRupiah = (angka: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Filter Pencarian (berdasarkan ID transaksi atau nama kasir)
const filteredTransactions = computed(() => {
    if (!searchQuery.value) return props.transactions;
    const query = searchQuery.value.toLowerCase();
    return props.transactions.filter(t =>
        t.id.toString().includes(query) ||
        t.user.name.toLowerCase().includes(query)
    );
});

// Fungsi Melihat Detail & Struk
const viewDetails = async (id: number) => {
    isLoadingDetails.value = true;
    try {
        // Memanggil route show dengan header JSON agar mendapatkan data saja
        const response = await fetch(route('transactions.show', { transaction: id }), {
            headers: { 'Accept': 'application/json' }
        });
        const data = await response.json();
        selectedTransaction.value = data.transaction;
        isModalOpen.value = true;
    } catch {
        alert('Gagal mengambil detail transaksi');
    } finally {
        isLoadingDetails.value = false;
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    selectedTransaction.value = null;
};

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <Head title="Riwayat Transaksi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-none p-4">

            <!-- Area Header & Pencarian -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-none border border-sidebar-border/70 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Riwayat Transaksi</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Lihat dan cetak ulang struk belanja pelanggan</p>
                </div>
                <div class="relative w-full md:w-80">
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Cari ID atau nama kasir..."
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-none shadow-sm pl-10 focus:border-blue-500 focus:ring-blue-500 focus:outline-none"
                    >
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </div>
                </div>
            </div>

            <!-- Tabel Riwayat -->
            <div class="bg-white dark:bg-gray-800 rounded-none border border-sidebar-border/70 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase">ID Transaksi</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-300 uppercase">Kasir</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-300 uppercase">Total Belanja</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="t in filteredTransactions" :key="t.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 dark:text-blue-400">#{{ t.id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ formatDate(t.date) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400 font-medium">{{ t.user.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-gray-800 dark:text-white">{{ formatRupiah(t.total) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button
                                        @click="viewDetails(t.id)"
                                        :disabled="isLoadingDetails"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 rounded-none hover:bg-indigo-100 transition text-xs font-bold"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Detail Struk
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredTransactions.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                    Tidak ada data transaksi ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL DETAIL STRUK (REUSE DARI KASIR) -->
        <div v-if="isModalOpen && selectedTransaction" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-none shadow-2xl w-full max-w-sm overflow-hidden flex flex-col max-h-screen">

                <!-- Area Struk -->
                <div id="print-area" class="p-6 bg-white text-black overflow-y-auto font-mono text-xs">
                    <div class="text-center mb-4 border-b-2 border-dashed border-gray-300 pb-4">
                        <h2 class="text-lg font-bold uppercase tracking-widest">TOKO KITA POS</h2>
                        <p class="text-[10px] text-gray-500 mt-1">Jl. Contoh Point of Sales No. 123</p>
                    </div>

                    <div class="flex justify-between mb-4">
                        <div>
                            <p>No: #{{ selectedTransaction.id }}</p>
                            <p>Tgl: {{ formatDate(selectedTransaction.date) }}</p>
                            <p>Kasir: {{ selectedTransaction.user.name }}</p>
                        </div>
                    </div>

                    <div class="border-b border-dashed border-gray-300 pb-2 mb-2">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left border-b border-gray-100">
                                    <th class="pb-1 w-1/2">Item</th>
                                    <th class="pb-1 text-center">Qty</th>
                                    <th class="pb-1 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="detail in selectedTransaction.details" :key="detail.id">
                                    <td class="py-1 pr-2 align-top">{{ detail.item.name }}</td>
                                    <td class="py-1 text-center align-top">{{ detail.qty }}</td>
                                    <td class="py-1 text-right align-top">{{ formatRupiah(detail.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-1 pt-2">
                        <div class="flex justify-between font-bold text-sm">
                            <span>TOTAL:</span>
                            <span>{{ formatRupiah(selectedTransaction.total) }}</span>
                        </div>
                        <div class="flex justify-between opacity-80">
                            <span>Tunai:</span>
                            <span>{{ formatRupiah(selectedTransaction.paid) }}</span>
                        </div>
                        <div class="flex justify-between opacity-80">
                            <span>Kembali:</span>
                            <span>{{ formatRupiah(selectedTransaction.change) }}</span>
                        </div>
                    </div>

                    <div class="text-center mt-6 pt-4 border-t-2 border-dashed border-gray-300">
                        <p>--- REPRINT / CETAK ULANG ---</p>
                        <p class="mt-2 uppercase">Terima Kasih</p>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="bg-gray-100 p-4 border-t flex justify-end gap-2 no-print">
                    <button @click="closeModal" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-none font-bold text-xs transition">
                        Tutup
                    </button>
                    <button @click="printReceipt" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-none font-bold text-xs transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>

        <!-- CSS Print Styling -->
        <component :is="'style'">
            @media print {
                body * {
                    visibility: hidden;
                }
                #print-area, #print-area * {
                    visibility: visible;
                }
                #print-area {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                }
                .no-print {
                    display: none !important;
                }
            }
        </component>

    </AppLayout>
</template>
