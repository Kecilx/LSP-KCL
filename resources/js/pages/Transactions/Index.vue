<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

// Setup Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transaksi Kasir',
        href: '/transactions',
    },
];

// Mendefinisikan antarmuka TypeScript
interface Category {
    id: number;
    name: string;
}

interface Item {
    id: number;
    category_id: number;
    name: string;
    price: number;
    stock: number;
    category?: Category;
}

interface CartItem {
    id: number;
    name: string;
    price: number;
    quantity: number;
    stock: number; // Untuk validasi batas maksimum
    subtotal: number;
}

// Menerima data dari TransactionController
const props = defineProps<{
    items: Item[];
    cashierName: string;
}>();

const page = usePage<any>();

// --- STATE MANAJEMEN KASIR ---
const searchQuery = ref('');
const cart = ref<CartItem[]>([]);
const paidAmount = ref<number | ''>('');

// State untuk Struk Pembayaran (Receipt Modal)
const showReceiptModal = ref(false);
const receiptData = ref<{
    items: CartItem[];
    total: number;
    paid: number;
    change: number;
    date: string;
} | null>(null);

// --- COMPUTED PROPERTIES ---

// Pencarian item
const filteredItems = computed(() => {
    if (!searchQuery.value) return props.items;
    return props.items.filter(item => 
        item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        item.category?.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

// Hitung Grand Total Otomatis
const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => total + item.subtotal, 0);
});

// Hitung Kembalian Otomatis
const changeAmount = computed(() => {
    const paid = Number(paidAmount.value) || 0;
    return paid - cartTotal.value;
});

// --- FUNGSI-FUNGSI LOGIKA KASIR ---

// Format Rupiah
const formatRupiah = (angka: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
};

// Tambah item ke keranjang
const addToCart = (item: Item) => {
    // Cek apakah item sudah ada di keranjang
    const existingItem = cart.value.find(c => c.id === item.id);

    if (existingItem) {
        // Jika sudah ada, tambah quantity jika stok masih mencukupi
        if (existingItem.quantity < item.stock) {
            existingItem.quantity++;
            existingItem.subtotal = existingItem.quantity * existingItem.price;
        } else {
            alert(`Stok maksimal untuk ${item.name} adalah ${item.stock}`);
        }
    } else {
        // Jika belum ada, masukkan item baru ke keranjang
        cart.value.push({
            id: item.id,
            name: item.name,
            price: Number(item.price),
            quantity: 1,
            stock: item.stock,
            subtotal: Number(item.price)
        });
    }
};

// Hapus item dari keranjang
const removeFromCart = (index: number) => {
    cart.value.splice(index, 1);
};

// Update quantity langsung dari input keranjang
const updateQuantity = (index: number, newQty: number) => {
    const item = cart.value[index];
    if (newQty > item.stock) {
        item.quantity = item.stock;
        alert(`Stok maksimal untuk ${item.name} adalah ${item.stock}`);
    } else if (newQty < 1) {
        item.quantity = 1;
    } else {
        item.quantity = newQty;
    }
    item.subtotal = item.quantity * item.price;
};

// Setup Form Submission menggunakan Inertia
const form = useForm({
    cart: [] as any[],
    total_amount: 0,
    paid_amount: 0,
});

// Proses Bayar (Checkout)
const processCheckout = () => {
    const paid = Number(paidAmount.value);
    
    // Validasi Sederhana sebelum dikirim ke backend
    if (cart.value.length === 0) {
        alert('Keranjang belanja masih kosong!');
        return;
    }
    if (paid < cartTotal.value) {
        alert('Uang pembayaran kurang dari total belanja!');
        return;
    }

    // Siapkan data untuk disubmit
    form.cart = cart.value.map(item => ({
        id: item.id,
        quantity: item.quantity,
        price: item.price,
        subtotal: item.subtotal
    }));
    form.total_amount = cartTotal.value;
    form.paid_amount = paid;

    // Simpan data untuk dicetak di struk sebelum keranjang dikosongkan
    const currentReceiptData = {
        items: JSON.parse(JSON.stringify(cart.value)), // Deep copy array
        total: cartTotal.value,
        paid: paid,
        change: changeAmount.value,
        date: new Date().toLocaleString('id-ID')
    };

    // Kirim request ke backend
    form.post(route('transactions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Jika berhasil, munculkan struk, kosongkan keranjang dan input bayar
            receiptData.value = currentReceiptData;
            showReceiptModal.value = true;
            cart.value = [];
            paidAmount.value = '';
        },
    });
};

// Fungsi Print Struk (Simulasi memanggil window.print)
const printReceipt = () => {
    window.print();
};

// Fungsi tutup modal struk
const closeReceiptModal = () => {
    showReceiptModal.value = false;
    receiptData.value = null;
};
</script>

<template>
    <Head title="Transaksi POS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
        <!-- Peringatan Alert dari Backend -->
        <div class="px-4 pt-4">
            <div v-if="page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-none relative shadow-sm" role="alert">
                <span class="block sm:inline">{{ page.props.flash.success }}</span>
            </div>
            <div v-if="page.props.flash?.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-none relative shadow-sm" role="alert">
                <span class="block sm:inline">{{ page.props.flash.error }}</span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row h-full gap-4 p-4 items-start">
            
            <!-- BAGIAN KIRI: DAFTAR PRODUK (KATALOG) -->
            <div class="w-full lg:w-2/3 flex flex-col gap-4">
                
                <!-- Pencarian Item -->
                <div class="bg-white dark:bg-gray-800 p-4 rounded-none shadow-sm border border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">Pilih Produk</h2>
                    <input 
                        type="text" 
                        v-model="searchQuery" 
                        placeholder="Cari nama atau kategori..." 
                        class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-none shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:outline-none w-64"
                    >
                </div>

                <!-- Grid Item Produk -->
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 overflow-y-auto max-h-[calc(100vh-250px)] pr-2">
                    <div 
                        v-for="item in filteredItems" 
                        :key="item.id"
                        @click="addToCart(item)"
                        class="bg-white dark:bg-gray-800 rounded-none border border-gray-200 dark:border-gray-700 p-4 shadow-sm hover:shadow-md cursor-pointer transition flex flex-col justify-between"
                        :class="{'opacity-50 pointer-events-none': item.stock === 0}"
                    >
                        <div>
                            <span class="text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 px-2 py-1 rounded-none mb-2 inline-block">
                                {{ item.category?.name || 'Umum' }}
                            </span>
                            <h3 class="font-bold text-gray-800 dark:text-white mt-1 leading-tight">{{ item.name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Stok: {{ item.stock }}</p>
                        </div>
                        <div class="mt-3 text-right font-bold text-indigo-600 dark:text-indigo-400">
                            {{ formatRupiah(item.price) }}
                        </div>
                    </div>
                    
                    <div v-if="filteredItems.length === 0" class="col-span-full p-8 text-center text-gray-500 bg-white dark:bg-gray-800 rounded-none border border-dashed border-gray-300 dark:border-gray-700">
                        Tidak ada produk yang ditemukan / Stok sedang kosong.
                    </div>
                </div>
            </div>

            <!-- BAGIAN KANAN: KERANJANG BELANJA & CHECKOUT -->
            <div class="w-full lg:w-1/3 bg-white dark:bg-gray-800 rounded-none shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col h-full lg:max-h-[calc(100vh-130px)] sticky top-4">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50 rounded-none">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        Keranjang Belanja
                    </h2>
                    <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-none">{{ cart.length }} item</span>
                </div>

                <!-- List Keranjang (Scrollable) -->
                <div class="flex-1 p-4 overflow-y-auto space-y-4">
                    <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 text-center space-y-2 py-10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="opacity-50"><path d="m6 2 2 8h11l2-8"/><path d="M6 10v9a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2v-9"/><path d="M12 14h.01"/></svg>
                        <p>Keranjang masih kosong.<br>Pilih produk di samping.</p>
                    </div>

                    <!-- Item di Keranjang -->
                    <div v-for="(cartItem, index) in cart" :key="cartItem.id" class="flex flex-col gap-2 p-3 border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 rounded-none">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold text-gray-800 dark:text-gray-100 text-sm leading-tight">{{ cartItem.name }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ formatRupiah(cartItem.price) }} / item</p>
                            </div>
                            <button @click="removeFromCart(index)" class="text-red-500 hover:text-red-700 p-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-none bg-white dark:bg-gray-800">
                                <button @click="updateQuantity(index, cartItem.quantity - 1)" class="px-2 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-none font-bold">-</button>
                                <input type="number" v-model.number="cartItem.quantity" @change="updateQuantity(index, cartItem.quantity)" class="w-12 text-center p-0 border-none text-sm bg-transparent dark:text-white focus:ring-0" min="1" :max="cartItem.stock">
                                <button @click="updateQuantity(index, cartItem.quantity + 1)" class="px-2 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-none font-bold">+</button>
                            </div>
                            <div class="font-bold text-gray-800 dark:text-white text-sm">
                                {{ formatRupiah(cartItem.subtotal) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian Total & Pembayaran (Sticky Bottom) -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-none">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600 dark:text-gray-400 font-semibold">Total Belanja:</span>
                        <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ formatRupiah(cartTotal) }}</span>
                    </div>

                    <div class="mt-4 mb-2 relative">
                        <label class="text-sm font-semibold text-gray-600 dark:text-gray-400 block mb-1">Uang Diterima (Tunai):</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-bold">Rp</span>
                            <input 
                                type="number" 
                                v-model="paidAmount" 
                                class="w-full pl-10 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-none shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:outline-none text-lg font-bold"
                                placeholder="0"
                                min="0"
                            >
                        </div>
                    </div>

                    <div class="flex justify-between items-center mb-4 text-sm">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">Kembalian:</span>
                        <span class="font-bold" :class="changeAmount < 0 ? 'text-red-500' : 'text-green-600 dark:text-green-400'">
                            {{ changeAmount < 0 ? 'Uang Kurang!' : formatRupiah(changeAmount) }}
                        </span>
                    </div>

                    <button 
                        @click="processCheckout" 
                        :disabled="cart.length === 0 || Number(paidAmount) < cartTotal || form.processing"
                        class="w-full py-3 rounded-none font-bold text-white transition-all shadow-md flex justify-center items-center gap-2"
                        :class="cart.length === 0 || Number(paidAmount) < cartTotal ? 'bg-gray-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700'"
                    >
                        <span v-if="form.processing">Memproses...</span>
                        <template v-else>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                            Bayar & Selesaikan
                        </template>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL STRUK (RECEIPT) -->
        <div v-if="showReceiptModal && receiptData" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4">
            <div class="bg-white rounded-none shadow-2xl w-full max-w-sm overflow-hidden flex flex-col max-h-screen">
                
                <!-- Area Struk yang bisa di-print -->
                <div id="print-area" class="p-6 bg-white text-black overflow-y-auto font-mono text-sm">
                    <div class="text-center mb-4 border-b-2 border-dashed border-gray-300 pb-4">
                        <h2 class="text-xl font-bold uppercase tracking-widest">TOKO KITA POS</h2>
                        <p class="text-xs text-gray-500 mt-1">Jl. Contoh Point of Sales No. 123</p>
                    </div>
                    
                    <div class="flex justify-between text-xs mb-4">
                        <div>
                            <p>Tgl: {{ receiptData.date }}</p>
                            <p>Kasir: {{ cashierName }}</p>
                        </div>
                    </div>

                    <div class="border-b border-dashed border-gray-300 pb-2 mb-2">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left">
                                    <th class="pb-1 w-1/2">Item</th>
                                    <th class="pb-1 text-center">Qty</th>
                                    <th class="pb-1 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in receiptData.items" :key="item.id">
                                    <td class="py-1 pr-2 align-top break-words">{{ item.name }}</td>
                                    <td class="py-1 text-center align-top">{{ item.quantity }}</td>
                                    <td class="py-1 text-right align-top">{{ formatRupiah(item.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col gap-1 text-sm pt-2">
                        <div class="flex justify-between font-bold">
                            <span>TOTAL:</span>
                            <span>{{ formatRupiah(receiptData.total) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tunai:</span>
                            <span>{{ formatRupiah(receiptData.paid) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Kembali:</span>
                            <span>{{ formatRupiah(receiptData.change) }}</span>
                        </div>
                    </div>

                    <div class="text-center mt-6 pt-4 border-t-2 border-dashed border-gray-300 text-xs">
                        <p>Terima Kasih Atas Kunjungan Anda!</p>
                        <p class="mt-1">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
                    </div>
                </div>

                <!-- Tombol Action (Tidak di-print) -->
                <div class="bg-gray-100 p-4 border-t flex justify-end gap-2 no-print">
                    <button @click="closeReceiptModal" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-none font-semibold transition text-sm">
                        Tutup (Transaksi Baru)
                    </button>
                    <button @click="printReceipt" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-none font-semibold transition text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>

        <!-- CSS untuk menyembunyikan elemen lain saat nge-print -->
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
                    padding: 0;
                    margin: 0;
                    box-shadow: none;
                }
                .no-print {
                    display: none !important;
                }
            }
        </component>

    </AppLayout>
</template>