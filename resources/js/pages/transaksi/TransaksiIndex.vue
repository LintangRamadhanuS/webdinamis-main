<script setup>
import { onMounted, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import EmptyState from '../../components/ui/EmptyState.vue';
import Pagination from '../../components/ui/Pagination.vue';
import SkeletonRow from '../../components/ui/SkeletonRow.vue';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';
import Icon from '../../components/ui/Icon.vue';
import TransaksiFormModal from '../../components/transaksi/TransaksiFormModal.vue';
import { useTransaksi } from '../../composables/useTransaksi';
import { useToast } from '../../composables/useToast';
import api from '../../lib/axios';

const { items, loading, error, meta, filters, fetchTransaksi, goToPage, createTransaksi, updateTransaksi, deleteTransaksi } = useTransaksi();
const toast = useToast();

const users = ref([]);
const barangs = ref([]);

const showForm = ref(false);
const editingTransaksi = ref(null);
const saving = ref(false);
const serverErrors = ref({});

const showConfirm = ref(false);
const deletingId = ref(null);
const deleting = ref(false);

const statusStyle = {
    pending: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    diproses: 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400',
    selesai: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
    dibatalkan: 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400',
};
const statusFilters = [
    { value: '', label: 'Semua' },
    { value: 'pending', label: 'Pending' },
    { value: 'diproses', label: 'Diproses' },
    { value: 'selesai', label: 'Selesai' },
    { value: 'dibatalkan', label: 'Dibatalkan' },
];

onMounted(async () => {
    fetchTransaksi();
    try {
        const [userRes, barangRes] = await Promise.all([
            api.get('/api/user', { params: { per_page: 50 } }),
            api.get('/api/barang', { params: { per_page: 50 } }),
        ]);
        users.value = userRes.data.data;
        barangs.value = barangRes.data.data;
    } catch {
        toast.error('Gagal memuat data pendukung (pengguna/barang).');
    }
});

// "YYYY-MM-DD HH:mm:ss" -> "7 Agu 2026, 14:23" — manipulasi string langsung, bebas isu timezone.
function formatTanggal(str) {
    if (!str) return '-';
    const [datePart, timePart] = str.split(' ');
    const [y, m, d] = datePart.split('-').map(Number);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return `${d} ${months[m - 1]} ${y}, ${(timePart ?? '').slice(0, 5)}`;
}

// Ubah status langsung dari dropdown di tabel tanpa buka modal.
async function quickUpdateStatus(t, newStatus) {
    if (newStatus === t.status) return;
    try {
        const updated = await updateTransaksi(t.id, { status: newStatus });
        t.status = updated.status;
        toast.success('Status transaksi diperbarui.');
    } catch {
        toast.error('Gagal memperbarui status.');
    }
}

function openCreate() {
    editingTransaksi.value = null;
    serverErrors.value = {};
    showForm.value = true;
}

function openEdit(t) {
    editingTransaksi.value = t;
    serverErrors.value = {};
    showForm.value = true;
}

async function handleSubmit(payload) {
    saving.value = true;
    serverErrors.value = {};
    try {
        if (editingTransaksi.value) {
            await updateTransaksi(editingTransaksi.value.id, payload);
            toast.success('Transaksi berhasil diperbarui.');
        } else {
            await createTransaksi(payload);
            toast.success('Transaksi berhasil dicatat.');
        }
        showForm.value = false;
        fetchTransaksi();
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
        await deleteTransaksi(deletingId.value);
        toast.success('Transaksi berhasil dihapus.');
    } catch {
        toast.error('Gagal menghapus transaksi.');
    } finally {
        deleting.value = false;
        showConfirm.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <PageHeader title="Transaksi" subtitle="Pantau dan kelola transaksi jual-beli barang.">
            <template #actions>
                <button
                    @click="openCreate"
                    class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2.5 text-sm font-medium text-white shadow-md shadow-amber-200 transition-all hover:shadow-lg active:scale-95 dark:shadow-amber-950"
                >
                    <Icon name="plus" class="h-4 w-4" /> Catat Transaksi
                </button>
            </template>
        </PageHeader>

        <div class="mb-4 flex animate-fade-in-up flex-wrap gap-2" style="animation-delay: 80ms">
            <button
                v-for="opt in statusFilters" :key="opt.value" @click="filters.status = opt.value"
                class="rounded-full px-4 py-2 text-sm font-medium transition-all duration-200"
                :class="filters.status === opt.value ? 'bg-slate-900 text-white shadow-md shadow-slate-300 dark:bg-white dark:text-slate-900 dark:shadow-none' : 'bg-white text-slate-600 ring-1 ring-slate-900/5 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300 dark:ring-white/10 dark:hover:bg-slate-800'"
            >
                {{ opt.label }}
            </button>
        </div>

        <p v-if="error" class="mb-4 flex items-center gap-2 rounded-lg bg-rose-50 px-4 py-2.5 text-sm text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
            <Icon name="alert" class="h-4 w-4 shrink-0" /> {{ error }}
        </p>

        <div class="animate-fade-in-up overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10" style="animation-delay: 140ms">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Barang</th>
                        <th class="px-4 py-3">Pembeli</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <template v-if="loading">
                        <SkeletonRow v-for="n in 5" :key="n" :cols="5" />
                    </template>
                    <tr v-else-if="items.length === 0">
                        <td colspan="5">
                            <EmptyState icon="swap" title="Belum ada transaksi" description="Transaksi yang tercatat akan muncul di sini." />
                        </td>
                    </tr>
                    <tr
                        v-else v-for="(t, i) in items" :key="t.id"
                        class="animate-fade-in-up transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/60"
                        :style="{ animationDelay: `${i * 40}ms` }"
                    >
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ t.barang?.nama ?? 'Barang telah dihapus' }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ t.user?.name ?? 'Pengguna telah dihapus' }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ formatTanggal(t.tanggal) }}</td>
                        <td class="px-4 py-3">
                            <div class="relative inline-block">
                                <select
                                    :value="t.status" @change="quickUpdateStatus(t, $event.target.value)"
                                    class="cursor-pointer appearance-none rounded-full py-1.5 pl-3 pr-7 text-xs font-medium capitalize transition-colors focus:outline-none"
                                    :class="statusStyle[t.status]"
                                >
                                    <option value="pending">pending</option>
                                    <option value="diproses">diproses</option>
                                    <option value="selesai">selesai</option>
                                    <option value="dibatalkan">dibatalkan</option>
                                </select>
                                <Icon name="chevron-down" class="pointer-events-none absolute right-2 top-1/2 h-3 w-3 -translate-y-1/2 opacity-60" />
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <button @click="openEdit(t)" title="Edit tanggal" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:text-slate-500 dark:hover:bg-indigo-500/15 dark:hover:text-indigo-400">
                                    <Icon name="pencil" class="h-4 w-4" />
                                </button>
                                <button @click="askDelete(t.id)" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-slate-500 dark:hover:bg-rose-500/15 dark:hover:text-rose-400">
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

        <TransaksiFormModal
            :show="showForm" :transaksi="editingTransaksi" :users="users" :barangs="barangs"
            :saving="saving" :server-errors="serverErrors" @submit="handleSubmit" @close="showForm = false"
        />
        <ConfirmModal
            :show="showConfirm" title="Hapus Transaksi" message="Transaksi yang dihapus tidak dapat dikembalikan. Lanjutkan?"
            :loading="deleting" @confirm="confirmDelete" @cancel="showConfirm = false"
        />
    </AppLayout>
</template>
