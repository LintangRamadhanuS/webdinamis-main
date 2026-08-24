<script setup>
import { onMounted } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import EmptyState from '../../components/ui/EmptyState.vue';
import Pagination from '../../components/ui/Pagination.vue';
import Icon from '../../components/ui/Icon.vue';
import { useUlasan } from '../../composables/useUlasan';

// Sama seperti MyTransaksi -- backend otomatis men-scope ke ulasan milik sendiri untuk non-admin.
const { items, loading, error, meta, fetchUlasan, goToPage } = useUlasan();

function timeAgo(dateStr) {
    if (!dateStr) return '';
    const diff = (Date.now() - new Date(dateStr.replace(' ', 'T'))) / 1000;
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`;
    if (diff < 2592000) return `${Math.floor(diff / 86400)} hari lalu`;
    return new Date(dateStr.replace(' ', 'T')).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

onMounted(fetchUlasan);
</script>

<template>
    <AppLayout>
        <PageHeader title="Ulasan Saya" subtitle="Ulasan yang pernah kamu berikan untuk barang." />

        <p v-if="error" class="mb-4 flex items-center gap-2 rounded-lg bg-rose-50 px-4 py-2.5 text-sm text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
            <Icon name="alert" class="h-4 w-4 shrink-0" /> {{ error }}
        </p>

        <div v-if="loading" class="space-y-3">
            <div v-for="i in 3" :key="i" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10">
                <div class="skeleton h-3.5 w-1/3 rounded"></div>
                <div class="skeleton mt-3 h-4 w-24 rounded"></div>
                <div class="skeleton mt-3 h-10 w-full rounded"></div>
            </div>
        </div>

        <div v-else-if="!items.length" class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10">
            <EmptyState icon="star" title="Belum ada ulasan" description="Ulasan yang kamu berikan untuk barang akan tampil di sini." />
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="(u, i) in items" :key="u.id"
                class="animate-fade-in-up rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10"
                :style="{ animationDelay: `${i * 60}ms` }"
            >
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ u.barang?.nama ?? 'Barang telah dihapus' }}</p>
                <p class="mb-2 text-xs text-slate-400 dark:text-slate-500">{{ timeAgo(u.created_at) }}</p>
                <div class="flex items-center gap-0.5">
                    <Icon v-for="n in 5" :key="n" name="star" class="h-4 w-4 fill-current" :class="n <= u.rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700'" />
                </div>
                <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ u.komentar }}</p>
            </div>
        </div>

        <div class="mt-5">
            <Pagination :current-page="meta.current_page" :last-page="meta.last_page" @change="goToPage" />
        </div>
    </AppLayout>
</template>
