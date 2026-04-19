<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import Swal, { type SweetAlertResult } from 'sweetalert2'; // Import SweetAlert2 dan tipenya

// Setup Breadcrumbs untuk AppLayout
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Master Kategori',
        href: '/categories',
    },
];

// Mendefinisikan tipe data/struktur Category
interface Category {
    id: number;
    name: string;
}

// Menerima data dari CategoryController menggunakan format TypeScript
defineProps<{
    categories: Category[];
}>();

// Mengambil page data dari Inertia (untuk membaca flash message dari backend)
const page = usePage<any>();

// Setup Form menggunakan Inertia
const form = useForm({
    name: '',
});

// State untuk Modal dengan explicit type
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingId = ref<number | null>(null);

// Fungsi untuk membuka modal Tambah
const openAddModal = () => {
    isEditMode.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

// Fungsi untuk membuka modal Edit
const openEditModal = (category: Category) => {
    isEditMode.value = true;
    editingId.value = category.id;
    form.name = category.name;
    form.clearErrors();
    isModalOpen.value = true;
};

// Fungsi untuk menutup modal
const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

// Fungsi Submit (Tambah / Edit) dengan SweetAlert
const submitData = () => {
    if (isEditMode.value && editingId.value !== null) {
        // Menggunakan format Objek untuk parameter rute agar sesuai dengan definisi RouteParams Ziggy
        form.put(route('categories.update', { category: editingId.value }), {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Kategori berhasil diperbarui!',
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonColor: '#3085d6'
                });
            },
        });
    } else {
        form.post(route('categories.store'), {
            onSuccess: () => {
                closeModal();
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Kategori baru berhasil ditambahkan!',
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonColor: '#3085d6'
                });
            },
        });
    }
};

// Fungsi Hapus dengan konfirmasi SweetAlert2
const deleteCategory = (id: number) => {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Kategori ini akan dihapus!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444', // red-500
        cancelButtonColor: '#6b7280', // gray-500
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result: SweetAlertResult) => {
        if (result.isConfirmed) {
            router.delete(route('categories.destroy', { category: id }), {
                onSuccess: () => {
                    // Cek jika controller mengembalikan error (misal: kategori masih digunakan oleh item)
                    if (page.props.flash?.error) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: page.props.flash.error,
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        Swal.fire({
                            title: 'Terhapus!',
                            text: 'Kategori berhasil dihapus.',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Master Kategori" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-none p-4">

            <!-- Konten Utama -->
            <div class="relative flex-1 rounded-none border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-gray-800/40 p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Daftar Kategori</h3>
                    <button @click="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-none transition">
                        + Tambah Kategori
                    </button>
                </div>

                <!-- Tabel Kategori -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-700 border-b dark:border-gray-600">
                                <th class="py-3 px-4 text-left font-semibold text-gray-600 dark:text-gray-300 w-16">No</th>
                                <th class="py-3 px-4 text-left font-semibold text-gray-600 dark:text-gray-300">Nama Kategori</th>
                                <th class="py-3 px-4 text-center font-semibold text-gray-600 dark:text-gray-300 w-48">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(category, index) in categories" :key="category.id" class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600/50 transition">
                                <td class="py-3 px-4 dark:text-gray-300">{{ index + 1 }}</td>
                                <td class="py-3 px-4 dark:text-gray-300">{{ category.name }}</td>
                                <td class="py-3 px-4 text-center">
                                    <button @click="openEditModal(category)" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded-none mr-2 text-sm transition">
                                        Edit
                                    </button>
                                    <button @click="deleteCategory(category.id)" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded-none text-sm transition">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="categories.length === 0">
                                <td colspan="3" class="py-4 text-center text-gray-500 dark:text-gray-400">Belum ada data kategori.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Tambah / Edit -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-50">
            <!-- Modal Container dengan outline tipis dan bersudut lancip -->
            <div class="bg-white dark:bg-gray-800 rounded-none border border-gray-300 dark:border-gray-600 shadow-xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700/50">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ isEditMode ? 'Edit Kategori' : 'Tambah Kategori' }}</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 font-bold text-xl">&times;</button>
                </div>
                
                <form @submit.prevent="submitData">
                    <div class="px-6 py-5">
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Kategori <span class="text-red-500">*</span></label>
                            <input 
                                type="text" 
                                id="name" 
                                v-model="form.name" 
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500 focus:outline-none rounded-none shadow-sm"
                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.name }"
                                placeholder="Masukkan nama kategori"
                                required
                            >
                            <!-- Pesan Error Validasi -->
                            <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
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