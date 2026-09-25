<script setup>
import { onMounted, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import EmptyState from '../../components/ui/EmptyState.vue';
import Pagination from '../../components/ui/Pagination.vue';
import SkeletonRow from '../../components/ui/SkeletonRow.vue';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';
import Icon from '../../components/ui/Icon.vue';
import UlasanFormModal from '../../components/ulasan/UlasanFormModal.vue';
import { useTransaksi } from '../../composables/useTransaksi';
import { useToast } from '../../composables/useToast';
import api from '../../lib/axios';

// Composable yang sama dengan admin -- backend (TransaksiController@index) otomatis
// men-scope hasilnya ke transaksi milik user yang login saat role bukan admin.
const { items, loading, error, meta, filters, fetchTransaksi, goToPage, batalkanTransaksi } = useTransaksi();
const toast = useToast();

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

const reviewingTransaksi = ref(null);
const showReviewForm = ref(false);
const savingReview = ref(false);

const cancelingTransaksi = ref(null);
const showCancelConfirm = ref(false);
const canceling = ref(false);

onMounted(fetchTransaksi);

function formatTanggal(str) {
    if (!str) return '-';
    const d = new Date(str);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}, ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
}

function openReview(t) {
    reviewingTransaksi.value = t;
    showReviewForm.value = true;
}

async function handleReviewSubmit(payload) {
    savingReview.value = true;
    try {
        await api.post(`/api/barang/${reviewingTransaksi.value.barang.id}/ulasan`, payload);
        toast.success('Ulasan berhasil dikirim, terima kasih!');
        showReviewForm.value = false;
    } catch (err) {
        // Backend menolak (403/422) kalau belum "selesai" atau sudah pernah diulas -- tampilkan pesan aslinya.
        toast.error(err.response?.data?.message ?? 'Gagal mengirim ulasan.');
    } finally {
        savingReview.value = false;
    }
}

function askCancel(t) {
    cancelingTransaksi.value = t;
    showCancelConfirm.value = true;
}

async function confirmCancel() {
    canceling.value = true;
    try {
        await batalkanTransaksi(cancelingTransaksi.value.id);
        toast.success('Transaksi berhasil dibatalkan.');
        fetchTransaksi(); // refresh -- status & badge di baris terkait ikut ter-update
    } catch (err) {
        toast.error(err.response?.data?.message ?? 'Gagal membatalkan transaksi.');
    } finally {
        canceling.value = false;
        showCancelConfirm.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <PageHeader title="Transaksi Saya" subtitle="Riwayat transaksi barang yang pernah kamu beli." />

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
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <template v-if="loading">
                        <SkeletonRow v-for="n in 5" :key="n" :cols="4" />
                    </template>
                    <tr v-else-if="items.length === 0">
                        <td colspan="4">
                            <EmptyState icon="swap" title="Belum ada transaksi" description="Riwayat pembelianmu akan muncul di sini." />
                        </td>
                    </tr>
                    <tr
                        v-else v-for="(t, i) in items" :key="t.id"
                        class="animate-fade-in-up transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/60"
                        :style="{ animationDelay: `${i * 40}ms` }"
                    >
                        <td class="px-4 py-3 font-medium text-slate-800 dark:text-slate-100">{{ t.barang?.nama ?? 'Barang telah dihapus' }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ formatTanggal(t.tanggal) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium capitalize" :class="statusStyle[t.status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'">
                                {{ t.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    v-if="t.status === 'pending' || t.status === 'diproses'"
                                    @click="askCancel(t)"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/15"
                                >
                                    <Icon name="x" class="h-3.5 w-3.5" /> Batalkan
                                </button>
                                <button
                                    v-if="t.status === 'selesai' && t.barang"
                                    @click="openReview(t)"
                                    class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-amber-600 transition-colors hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-500/15"
                                >
                                    <Icon name="star" class="h-3.5 w-3.5" /> Beri Ulasan
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

        <UlasanFormModal
            :show="showReviewForm" :barang-nama="reviewingTransaksi?.barang?.nama ?? ''" :saving="savingReview"
            @submit="handleReviewSubmit" @close="showReviewForm = false"
        />

        <ConfirmModal
            :show="showCancelConfirm" title="Batalkan Transaksi"
            :message="`Batalkan pembelian ${cancelingTransaksi?.barang?.nama ?? 'barang ini'}? Barang akan tersedia kembali untuk pembeli lain.`"
            confirm-text="Batalkan" confirming-text="Membatalkan..."
            :loading="canceling" @confirm="confirmCancel" @cancel="showCancelConfirm = false"
        />
    </AppLayout>
</template>
