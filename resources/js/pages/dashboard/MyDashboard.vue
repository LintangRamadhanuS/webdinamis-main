<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import StatCard from '../../components/dashboard/StatCard.vue';
import EmptyState from '../../components/ui/EmptyState.vue';
import Icon from '../../components/ui/Icon.vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../lib/axios';

const auth = useAuthStore();

const loadingStats = ref(true);
const loadingActivity = ref(true);
const totals = ref({});
const activity = ref([]);

function transaksiStatusClass(status) {
    return {
        pending: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        diproses: 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400',
        selesai: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
        dibatalkan: 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400',
    }[status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
}

function timeAgo(dateStr) {
    if (!dateStr) return '';
    const diff = (Date.now() - new Date(dateStr.replace(' ', 'T'))) / 1000;
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`;
    if (diff < 2592000) return `${Math.floor(diff / 86400)} hari lalu`;
    return new Date(dateStr.replace(' ', 'T')).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

async function loadStats() {
    try {
        const { data } = await api.get('/api/dashboard/my-stats');
        totals.value = data.totals;
    } finally {
        loadingStats.value = false;
    }
}

async function loadActivity() {
    try {
        const { data } = await api.get('/api/dashboard/my-activity');
        activity.value = data.data;
    } finally {
        loadingActivity.value = false;
    }
}

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 11) return 'Selamat pagi';
    if (h < 15) return 'Selamat siang';
    if (h < 19) return 'Selamat sore';
    return 'Selamat malam';
});
const firstName = computed(() => auth.user?.name?.split(' ')[0] ?? 'Pengguna');

const statCards = computed(() => [
    { label: 'Total Transaksi', value: totals.value.transaksi, icon: 'swap', color: 'indigo', to: { name: 'transaksi.saya' } },
    { label: 'Transaksi Selesai', value: totals.value.transaksi_selesai, icon: 'check-circle', color: 'emerald', to: { name: 'transaksi.saya' } },
    { label: 'Sedang Berjalan', value: totals.value.transaksi_berjalan, icon: 'clock', color: 'amber', to: { name: 'transaksi.saya' } },
    { label: 'Ulasan Saya', value: totals.value.ulasan, icon: 'star', color: 'rose', to: { name: 'ulasan.saya' } },
]);

let pollInterval = null;

onMounted(() => {
    loadStats();
    loadActivity();
    pollInterval = setInterval(() => { loadStats(); loadActivity(); }, 30000);
});
onUnmounted(() => clearInterval(pollInterval));
</script>

<template>
    <AppLayout>
        <div class="mb-8 animate-fade-in-up">
            <h1 class="flex items-center gap-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                {{ greeting }}, <span class="text-gradient-brand">{{ firstName }}</span>
                <Icon name="sparkles" class="h-6 w-6 animate-float text-amber-400" />
            </h1>
            <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">Ringkasan transaksi &amp; ulasan milikmu di marketplace Bekas.</p>
        </div>

        <RouterLink
            :to="{ name: 'katalog.index' }"
            class="group mb-6 flex animate-fade-in-up items-center justify-between overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 p-5 text-white shadow-lg shadow-indigo-200 transition-transform duration-300 hover:-translate-y-0.5"
            style="animation-delay: 100ms"
        >
            <div class="flex items-center gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15">
                    <Icon name="box" class="h-5 w-5" />
                </div>
                <div>
                    <p class="font-semibold">Jelajahi Barang Tersedia</p>
                    <p class="text-sm text-indigo-100">Temukan barang bekas dari sesama mahasiswa</p>
                </div>
            </div>
            <Icon name="chevron-right" class="h-5 w-5 shrink-0 transition-transform duration-300 group-hover:translate-x-1" />
        </RouterLink>

        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <StatCard
                v-for="(s, i) in statCards" :key="s.label"
                :label="s.label" :value="s.value" :icon="s.icon" :color="s.color" :to="s.to"
                :loading="loadingStats" :delay="i * 70"
            />
        </div>

        <div class="animate-fade-in-up rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10" style="animation-delay: 280ms">
            <h3 class="mb-1 text-sm font-semibold text-slate-700 dark:text-slate-200">Aktivitas Saya</h3>

            <div v-if="loadingActivity" class="divide-y divide-slate-100 dark:divide-slate-800">
                <div v-for="i in 4" :key="i" class="flex items-center gap-3 py-3">
                    <div class="skeleton h-9 w-9 rounded-full"></div>
                    <div class="flex-1 space-y-2">
                        <div class="skeleton h-3.5 w-1/3 rounded"></div>
                        <div class="skeleton h-3 w-1/4 rounded"></div>
                    </div>
                </div>
            </div>

            <EmptyState v-else-if="!activity.length" icon="inbox" title="Belum ada aktivitas" description="Transaksi dan ulasanmu akan tampil di sini." />

            <ul v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                <li
                    v-for="(a, i) in activity" :key="`${a.type}-${a.id}`"
                    class="flex animate-fade-in-up items-center gap-3 py-3"
                    :style="{ animationDelay: `${i * 60}ms` }"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                        :class="a.type === 'transaksi' ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400'"
                    >
                        <Icon :name="a.type === 'transaksi' ? 'swap' : 'star'" class="h-4 w-4" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ a.title }}</p>
                        <p class="truncate text-xs text-slate-400 dark:text-slate-500">{{ timeAgo(a.at) }}</p>
                    </div>
                    <span
                        v-if="a.type === 'transaksi'"
                        class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                        :class="transaksiStatusClass(a.meta)"
                    >
                        {{ a.meta }}
                    </span>
                    <span v-else class="flex shrink-0 items-center gap-0.5 text-xs font-semibold text-amber-500">
                        <Icon name="star" class="h-3 w-3 fill-current" /> {{ a.meta }}
                    </span>
                </li>
            </ul>
        </div>
    </AppLayout>
</template>
