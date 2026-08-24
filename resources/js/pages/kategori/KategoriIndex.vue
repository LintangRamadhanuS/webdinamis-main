<script setup>
import { onMounted, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import EmptyState from '../../components/ui/EmptyState.vue';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';
import Icon from '../../components/ui/Icon.vue';
import KategoriFormModal from '../../components/kategori/KategoriFormModal.vue';
import { useKategori } from '../../composables/useKategori';
import { useToast } from '../../composables/useToast';

const { items, loading, error, fetchKategori, createKategori, updateKategori, deleteKategori } = useKategori();
const toast = useToast();

const showForm = ref(false);
const editingKategori = ref(null);
const saving = ref(false);
const serverErrors = ref({});

const showConfirm = ref(false);
const deletingId = ref(null);
const deleting = ref(false);

onMounted(fetchKategori);

function openCreate() {
    editingKategori.value = null;
    serverErrors.value = {};
    showForm.value = true;
}

function openEdit(k) {
    editingKategori.value = k;
    serverErrors.value = {};
    showForm.value = true;
}

async function handleSubmit(payload) {
    saving.value = true;
    serverErrors.value = {};
    try {
        if (editingKategori.value) {
            await updateKategori(editingKategori.value.id, payload);
            toast.success('Kategori berhasil diperbarui.');
        } else {
            await createKategori(payload);
            toast.success('Kategori berhasil ditambahkan.');
        }
        showForm.value = false;
        fetchKategori();
    } catch (err) {
        if (err.response?.status === 422) {
            serverErrors.value = err.response.data.errors;
        } else {
            toast.error('Terjadi kesalahan saat menyimpan data.');
        }
    } finally {
        saving.value = false;
    }
}

function askDelete(id) {
    deletingId.value = id;
    showConfirm.value = true;
}

async function confirmDelete() {
    deleting.value = true;
    try {
        await deleteKategori(deletingId.value);
        toast.success('Kategori berhasil dihapus.');
    } catch (err) {
        // Backend menolak (422) jika kategori masih punya barang terkait — tampilkan pesan aslinya.
        toast.error(err.response?.data?.message ?? 'Gagal menghapus kategori.');
    } finally {
        deleting.value = false;
        showConfirm.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <PageHeader title="Kategori" subtitle="Kelola kategori barang di marketplace.">
            <template #actions>
                <button
                    @click="openCreate"
                    class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-medium text-white shadow-md shadow-indigo-200 transition-all hover:shadow-lg active:scale-95 dark:shadow-indigo-950"
                >
                    <Icon name="plus" class="h-4 w-4" /> Tambah Kategori
                </button>
            </template>
        </PageHeader>

        <p v-if="error" class="mb-4 flex items-center gap-2 rounded-lg bg-rose-50 px-4 py-2.5 text-sm text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
            <Icon name="alert" class="h-4 w-4 shrink-0" /> {{ error }}
        </p>

        <div v-if="loading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div v-for="i in 8" :key="i" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10">
                <div class="skeleton h-11 w-11 rounded-xl"></div>
                <div class="skeleton mt-4 h-4 w-2/3 rounded"></div>
                <div class="skeleton mt-2 h-3 w-1/3 rounded"></div>
            </div>
        </div>

        <div v-else-if="!items.length" class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10">
            <EmptyState icon="tag" title="Belum ada kategori" description="Tambahkan kategori pertama untuk mulai mengelompokkan barang.">
                <button @click="openCreate" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Tambah Kategori
                </button>
            </EmptyState>
        </div>

        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
                v-for="(k, i) in items" :key="k.id"
                class="group relative animate-fade-in-up rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 lift-on-hover dark:bg-slate-900 dark:ring-white/10"
                :style="{ animationDelay: `${i * 50}ms` }"
            >
                <div class="flex items-start justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 text-white shadow-md shadow-sky-200 dark:shadow-sky-950">
                        <Icon name="tag" class="h-5 w-5" />
                    </div>
                    <div class="flex gap-1 opacity-100 transition-opacity duration-200 sm:opacity-0 sm:group-hover:opacity-100">
                        <button @click="openEdit(k)" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:text-slate-500 dark:hover:bg-indigo-500/15 dark:hover:text-indigo-400">
                            <Icon name="pencil" class="h-4 w-4" />
                        </button>
                        <button @click="askDelete(k.id)" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-slate-500 dark:hover:bg-rose-500/15 dark:hover:text-rose-400">
                            <Icon name="trash" class="h-4 w-4" />
                        </button>
                    </div>
                </div>
                <h3 class="mt-4 font-semibold text-slate-800 dark:text-slate-100">{{ k.nama_kategori }}</h3>
                <p class="mt-1 text-sm text-slate-400 dark:text-slate-500">{{ k.barangs_count }} barang</p>
            </div>
        </div>

        <KategoriFormModal
            :show="showForm" :kategori="editingKategori" :saving="saving" :server-errors="serverErrors"
            @submit="handleSubmit" @close="showForm = false"
        />
        <ConfirmModal
            :show="showConfirm" title="Hapus Kategori" message="Kategori yang masih memiliki barang tidak dapat dihapus. Lanjutkan?"
            :loading="deleting" @confirm="confirmDelete" @cancel="showConfirm = false"
        />
    </AppLayout>
</template>
