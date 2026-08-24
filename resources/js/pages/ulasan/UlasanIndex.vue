<script setup>
import { onMounted, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import EmptyState from '../../components/ui/EmptyState.vue';
import Pagination from '../../components/ui/Pagination.vue';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';
import Icon from '../../components/ui/Icon.vue';
import { useUlasan } from '../../composables/useUlasan';
import { useToast } from '../../composables/useToast';
import api from '../../lib/axios';

// Ulasan sengaja hanya "lihat + hapus" (moderasi) — ulasan secara alami ditulis oleh
// pengguna marketplace, jadi admin tidak mengetik ulasan palsu lewat panel ini.
const { items, loading, error, meta, filters, fetchUlasan, goToPage, deleteUlasan } = useUlasan();
const toast = useToast();

const barangs = ref([]);
const showConfirm = ref(false);
const deletingId = ref(null);
const deleting = ref(false);

onMounted(async () => {
    fetchUlasan();
    try {
        const { data } = await api.get('/api/barang', { params: { per_page: 50 } });
        barangs.value = data.data;
    } catch {
        toast.error('Gagal memuat daftar barang.');
    }
});

function timeAgo(dateStr) {
    if (!dateStr) return '';
    const diff = (Date.now() - new Date(dateStr.replace(' ', 'T'))) / 1000;
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`;
    if (diff < 2592000) return `${Math.floor(diff / 86400)} hari lalu`;
    return new Date(dateStr.replace(' ', 'T')).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function askDelete(id) {
    deletingId.value = id;
    showConfirm.value = true;
}

async function confirmDelete() {
    deleting.value = true;
    try {
        await deleteUlasan(deletingId.value);
        toast.success('Ulasan berhasil dihapus.');
    } catch {
        toast.error('Gagal menghapus ulasan.');
    } finally {
        deleting.value = false;
        showConfirm.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <PageHeader title="Ulasan" subtitle="Moderasi ulasan yang diberikan pengguna untuk barang." />

        <div class="mb-4 max-w-xs animate-fade-in-up" style="animation-delay: 80ms">
            <div class="relative">
                <select v-model="filters.barang_id" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                    <option value="">Semua Barang</option>
                    <option v-for="b in barangs" :key="b.id" :value="b.id">{{ b.nama }}</option>
                </select>
                <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
            </div>
        </div>

        <p v-if="error" class="mb-4 flex items-center gap-2 rounded-lg bg-rose-50 px-4 py-2.5 text-sm text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
            <Icon name="alert" class="h-4 w-4 shrink-0" /> {{ error }}
        </p>

        <div v-if="loading" class="space-y-3">
            <div v-for="i in 4" :key="i" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10">
                <div class="flex items-center gap-3">
                    <div class="skeleton h-10 w-10 rounded-full"></div>
                    <div class="flex-1 space-y-2">
                        <div class="skeleton h-3.5 w-1/4 rounded"></div>
                        <div class="skeleton h-3 w-1/3 rounded"></div>
                    </div>
                </div>
                <div class="skeleton mt-4 h-10 w-full rounded"></div>
            </div>
        </div>

        <div v-else-if="!items.length" class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10">
            <EmptyState icon="chat" title="Belum ada ulasan" description="Ulasan dari pengguna akan tampil di sini." />
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="(u, i) in items" :key="u.id"
                class="group animate-fade-in-up rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 transition-shadow hover:shadow-md dark:bg-slate-900 dark:ring-white/10"
                :style="{ animationDelay: `${i * 60}ms` }"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-400 to-pink-500 text-sm font-bold text-white">
                            {{ (u.user?.name ?? '?').charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ u.user?.name ?? 'Pengguna telah dihapus' }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500">
                                untuk <span class="font-medium text-slate-500 dark:text-slate-400">{{ u.barang?.nama ?? 'barang telah dihapus' }}</span> &middot; {{ timeAgo(u.created_at) }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="askDelete(u.id)" title="Hapus"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-300 opacity-100 transition-all hover:bg-rose-50 hover:text-rose-600 sm:opacity-0 sm:group-hover:opacity-100 dark:text-slate-600 dark:hover:bg-rose-500/15 dark:hover:text-rose-400"
                    >
                        <Icon name="trash" class="h-4 w-4" />
                    </button>
                </div>
                <div class="mt-3 flex items-center gap-0.5">
                    <Icon v-for="n in 5" :key="n" name="star" class="h-4 w-4 fill-current" :class="n <= u.rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700'" />
                </div>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ u.komentar }}</p>
            </div>
        </div>

        <div class="mt-5">
            <Pagination :current-page="meta.current_page" :last-page="meta.last_page" @change="goToPage" />
        </div>

        <ConfirmModal
            :show="showConfirm" title="Hapus Ulasan" message="Ulasan yang dihapus tidak dapat dikembalikan. Lanjutkan?"
            :loading="deleting" @confirm="confirmDelete" @cancel="showConfirm = false"
        />
    </AppLayout>
</template>
