<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import Swal, { type SweetAlertResult } from 'sweetalert2'; // Import SweetAlert2 dan tipenya

// Setup Breadcrumbs untuk AppLayout
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Master Item',
        href: '/items',
    },
];

// Mendefinisikan antarmuka/tipe data TypeScript
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

// Menerima data dari ItemController
defineProps<{
    items: Item[];
    categories: Category[];
}>();

// Setup Form Inertia
const form = useForm({
    category_id: '' as number | '',
    name: '',
    price: '' as number | '',
    stock: '' as number | '',
});

// State untuk Modal Form (Tambah/Edit)
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingId = ref<number | null>(null);

// Fungsi utilitas format harga ke Rupiah
const formatRupiah = (angka: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
};

// Fungsi membuka modal Tambah
const openAddModal = () => {
    isEditMode.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

// Fungsi membuka modal Edit
const openEditModal = (item: Item) => {
    isEditMode.value = true;
    editingId.value = item.id;
    form.category_id = item.category_id;
    form.name = item.name;
    form.price = item.price;
    form.stock = item.stock;
    form.clearErrors();
    isModalOpen.value = true;
};

// Fungsi menutup modal
const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

// Fungsi Submit (Tambah/Edit)
const submitData = () => {
    if (isEditMode.value && editingId.value !== null) {
        form.put(route('items.update', { item: editingId.value }), {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Data item berhasil diperbarui!',
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonColor: '#3085d6'
                });
            },
        });
    } else {
        form.post(route('items.store'), {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Item baru berhasil ditambahkan!',
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonColor: '#3085d6'
                });
            },
        });
    }
};

// Fungsi Hapus Item menggunakan SweetAlert2
const deleteItem = (id: number) => {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data item ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444', // red-500
        cancelButtonColor: '#6b7280', // gray-500
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result: SweetAlertResult) => { // Menambahkan tipe SweetAlertResult di sini
        if (result.isConfirmed) {
            router.delete(route('items.destroy', { item: id }), {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: 'Data item berhasil dihapus.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Master Item" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-none p-4">

            <div class="relative flex-1 rounded-none border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800/40 p-6 shadow-sm">
                <!-- Header Card -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Daftar Item Produk</h3>
                    <button @click="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-none transition">
                        + Tambah Item
                    </button>
                </div>

                <!-- Tabel Item -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-none">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 border-b dark:border-gray-600">
                                <th class="py-3 px-4 text-left font-semibold text-gray-600 dark:text-gray-300 w-12">No</th>
                                <th class="py-3 px-4 text-left font-semibold text-gray-600 dark:text-gray-300">Nama Item</th>
                                <th class="py-3 px-4 text-left font-semibold text-gray-600 dark:text-gray-300">Kategori</th>
                                <th class="py-3 px-4 text-right font-semibold text-gray-600 dark:text-gray-300">Harga</th>
                                <th class="py-3 px-4 text-center font-semibold text-gray-600 dark:text-gray-300">Stok</th>
                                <th class="py-3 px-4 text-center font-semibold text-gray-600 dark:text-gray-300 w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in items" :key="item.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600/50">
                                <td class="py-3 px-4 dark:text-gray-300">{{ index + 1 }}</td>
                                <td class="py-3 px-4 dark:text-gray-300 font-medium">{{ item.name }}</td>
                                <td class="py-3 px-4 dark:text-gray-300">
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-none dark:bg-indigo-900 dark:text-indigo-300">
                                        {{ item.category?.name || 'Kategori Terhapus' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 dark:text-gray-300 text-right">{{ formatRupiah(item.price) }}</td>
                                <td class="py-3 px-4 dark:text-gray-300 text-center">
                                    <span :class="{'text-red-500 font-bold': item.stock === 0}">{{ item.stock }}</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button @click="openEditModal(item)" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-none mr-2 text-sm transition">
                                        Edit
                                    </button>
                                    <button @click="deleteItem(item.id)" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-none text-sm transition">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="items.length === 0">
                                <td colspan="6" class="py-6 text-center text-gray-500 dark:text-gray-400">Belum ada data item produk.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Tambah / Edit -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-800 rounded-none border border-gray-300 dark:border-gray-600 shadow-xl w-full max-w-lg mx-4">
                <div class="px-6 py-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ isEditMode ? 'Edit Item' : 'Tambah Item' }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 font-bold text-xl">&times;</button>
                </div>

                <form @submit.prevent="submitData">
                    <div class="px-6 py-5 space-y-4">

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 focus:outline-none rounded-none shadow-sm"
                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.category_id }"
                                required
                            >
                                <option value="" disabled>-- Pilih Kategori --</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.category_id" class="text-red-500 text-xs mt-1">{{ form.errors.category_id }}</p>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Item <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="name"
                                v-model="form.name"
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 focus:outline-none rounded-none shadow-sm"
                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.name }"
                                placeholder="Masukkan nama item/produk"
                                required
                            >
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                                <input
                                    type="number"
                                    id="price"
                                    v-model="form.price"
                                    min="0"
                                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 focus:outline-none rounded-none shadow-sm"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.price }"
                                    placeholder="0"
                                    required
                                >
                                <p v-if="form.errors.price" class="text-red-500 text-xs mt-1">{{ form.errors.price }}</p>
                            </div>

                            <div>
                                <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stok Awal <span class="text-red-500">*</span></label>
                                <input
                                    type="number"
                                    id="stock"
                                    v-model="form.stock"
                                    min="0"
                                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 focus:outline-none rounded-none shadow-sm"
                                    :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.stock }"
                                    placeholder="0"
                                    required
                                >
                                <p v-if="form.errors.stock" class="text-red-500 text-xs mt-1">{{ form.errors.stock }}</p>
                            </div>
                        </div>

                    </div>

                    <div class="px-6 py-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-end">
                        <button type="button" @click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-none mr-2 transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-none transition disabled:opacity-50">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AppLayout>
</template>
