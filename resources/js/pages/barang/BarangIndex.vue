<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import EmptyState from '../../components/ui/EmptyState.vue';
import Pagination from '../../components/ui/Pagination.vue';
import SkeletonRow from '../../components/ui/SkeletonRow.vue';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';
import Icon from '../../components/ui/Icon.vue';
import BarangFormModal from '../../components/barang/BarangFormModal.vue';
import { useBarang } from '../../composables/useBarang';
import { useToast } from '../../composables/useToast';
import api from '../../lib/axios';

const { items, loading, error, meta, filters, fetchBarang, goToPage, createBarang, updateBarang, deleteBarang } = useBarang();
const toast = useToast();
const route = useRoute();
const router = useRouter();

const kategoris = ref([]);
const showForm = ref(false);
const editingBarang = ref(null);
const saving = ref(false);
const serverErrors = ref({});

const showConfirm = ref(false);
const deletingId = ref(null);
const deleting = ref(false);

const statusStyle = {
    tersedia: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
    terjual: 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    ditahan: 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
};

onMounted(async () => {
    fetchBarang();
    try {
        const { data } = await api.get('/api/kategori');
        kategoris.value = data;
    } catch {
        toast.error('Gagal memuat daftar kategori.');
    }

    // Datang dari quick action "Tambah Barang" di Dashboard (?new=1) -> langsung buka modal.
    if (route.query.new === '1') {
        openCreate();
        router.replace({ query: {} });
    }
});

function openCreate() {
    editingBarang.value = null;
    serverErrors.value = {};
    showForm.value = true;
}

function openEdit(barang) {
    editingBarang.value = barang;
    serverErrors.value = {};
    showForm.value = true;
}

async function handleSubmit(formData) {
    saving.value = true;
    serverErrors.value = {};
    try {
        if (editingBarang.value) {
            await updateBarang(editingBarang.value.id, formData);
            toast.success('Barang berhasil diperbarui.');
        } else {
            await createBarang(formData);
            toast.success('Barang berhasil ditambahkan.');
        }
        showForm.value = false;
        fetchBarang(); // refresh list agar data (termasuk relasi kategori) konsisten
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
        await deleteBarang(deletingId.value); // sudah update UI instan di dalam composable
        toast.success('Barang berhasil dihapus.');
    } catch {
        toast.error('Gagal menghapus barang.');
    } finally {
        deleting.value = false;
        showConfirm.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <PageHeader title="Barang" subtitle="Kelola seluruh barang yang dijual di marketplace.">
            <template #actions>
                <button
                    @click="openCreate"
                    class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-medium text-white shadow-md shadow-indigo-200 transition-all hover:shadow-lg active:scale-95 dark:shadow-indigo-950"
                >
                    <Icon name="plus" class="h-4 w-4" /> Tambah Barang
                </button>
            </template>
        </PageHeader>

        <!-- Filter bar -->
        <div class="mb-4 grid animate-fade-in-up grid-cols-1 gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 md:grid-cols-5 dark:bg-slate-900 dark:ring-white/10" style="animation-delay: 80ms">
            <div class="relative md:col-span-2">
                <Icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                <input
                    v-model="filters.q" type="text" placeholder="Cari nama atau deskripsi..."
                    class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                />
            </div>
            <div class="relative">
                <select v-model="filters.kategori_id" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                    <option value="">Semua Kategori</option>
                    <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
                </select>
                <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
            </div>
            <div class="relative">
                <select v-model="filters.status" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                    <option value="">Semua Status</option>
                    <option value="tersedia">Tersedia</option>
                    <option value="terjual">Terjual</option>
                    <option value="ditahan">Ditahan</option>
                </select>
                <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
            </div>
            <div class="relative">
                <select v-model="filters.sort" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                    <option value="terbaru">Terbaru</option>
                    <option value="terlama">Terlama</option>
                    <option value="harga_asc">Harga Terendah</option>
                    <option value="harga_desc">Harga Tertinggi</option>
                </select>
                <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
            </div>
            <div class="relative md:col-span-5 md:w-64">
                <select v-model="filters.kondisi" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                    <option value="">Semua Kondisi</option>
                    <option value="baru">Baru</option>
                    <option value="bekas - seperti baru">Bekas - seperti baru</option>
                    <option value="bekas - layak pakai">Bekas - layak pakai</option>
                    <option value="bekas - butuh perbaikan">Bekas - butuh perbaikan</option>
                </select>
                <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
            </div>
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 -translate-y-1"
        >
            <p v-if="error" class="mb-4 flex items-center gap-2 rounded-lg bg-rose-50 px-4 py-2.5 text-sm text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                <Icon name="alert" class="h-4 w-4 shrink-0" /> {{ error }}
            </p>
        </Transition>

        <div class="animate-fade-in-up overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10" style="animation-delay: 140ms">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Barang</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Harga</th>
                        <th class="px-4 py-3">Kondisi</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <template v-if="loading">
                        <SkeletonRow v-for="n in 5" :key="n" :cols="6" />
                    </template>
                    <tr v-else-if="items.length === 0">
                        <td colspan="6">
                            <EmptyState icon="box" title="Tidak ada barang yang cocok" description="Coba ubah kata kunci pencarian atau filter yang dipakai." />
                        </td>
                    </tr>
                    <tr
                        v-else v-for="(b, i) in items" :key="b.id"
                        class="animate-fade-in-up transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/60"
                        :style="{ animationDelay: `${i * 40}ms` }"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-slate-100 text-slate-300 dark:bg-slate-800 dark:text-slate-600">
                                    <img v-if="b.foto_url" :src="b.foto_url" class="h-full w-full object-cover" :alt="b.nama" />
                                    <Icon v-else name="image" class="h-5 w-5" />
                                </div>
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ b.nama }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ b.kategori?.nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200">{{ b.harga_format }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ b.kondisi }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium capitalize" :class="statusStyle[b.status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'">
                                {{ b.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <button
                                    @click="openEdit(b)" title="Edit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:text-slate-500 dark:hover:bg-indigo-500/15 dark:hover:text-indigo-400"
                                >
                                    <Icon name="pencil" class="h-4 w-4" />
                                </button>
                                <button
                                    @click="askDelete(b.id)" title="Hapus"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-slate-500 dark:hover:bg-rose-500/15 dark:hover:text-rose-400"
                                >
                                    <Icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            <Pagination :current-page="meta.current_page" :last-page="meta.last_page" @change="goToPage" />
        </div>

        <BarangFormModal
            :show="showForm" :barang="editingBarang" :kategoris="kategoris" :saving="saving" :server-errors="serverErrors"
            @submit="handleSubmit" @close="showForm = false"
        />

        <ConfirmModal
            :show="showConfirm" title="Hapus Barang" message="Barang yang dihapus tidak dapat dikembalikan. Lanjutkan?"
            :loading="deleting" @confirm="confirmDelete" @cancel="showConfirm = false"
        />
    </AppLayout>
</template>
