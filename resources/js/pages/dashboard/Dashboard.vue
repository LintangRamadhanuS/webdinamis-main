<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement,
    CategoryScale, LinearScale, ArcElement, Filler,
} from 'chart.js';
import AppLayout from '../../layouts/AppLayout.vue';
import StatCard from '../../components/dashboard/StatCard.vue';
import EmptyState from '../../components/ui/EmptyState.vue';
import Icon from '../../components/ui/Icon.vue';
import { useAuthStore } from '../../stores/auth';
import api from '../../lib/axios';

ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale, ArcElement, Filler);

const router = useRouter();
const auth = useAuthStore();

const loadingStats = ref(true);
const loadingActivity = ref(true);
const refreshing = ref(false);
const totals = ref({});
const recentActivity = ref([]);

const transaksiChartData = ref({ labels: [], datasets: [] });
const kategoriChartData = ref({ labels: [], datasets: [] });

const KATEGORI_COLORS = ['#4f46e5', '#0ea5e9', '#f59e0b', '#10b981', '#f43f5e', '#8b5cf6', '#ec4899', '#14b8a6'];

const baseChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: { duration: 900, easing: 'easeOutQuart' },
    plugins: {
        legend: { display: false },
        tooltip: { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8, displayColors: false },
    },
};

const lineChartOptions = {
    ...baseChartOptions,
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0, color: '#94a3b8' }, grid: { color: '#f1f5f9' } },
        x: { ticks: { color: '#94a3b8' }, grid: { display: false } },
    },
};

const doughnutChartOptions = { ...baseChartOptions, cutout: '72%' };

// Format "2026-07-15" -> "15 Jul" tanpa lewat Date() supaya bebas dari pergeseran timezone.
function formatDateLabel(isoDate) {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const [, m, d] = isoDate.split('-').map(Number);
    return `${d} ${months[m - 1]}`;
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

function transaksiStatusClass(status) {
    return {
        pending: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
        diproses: 'bg-sky-50 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400',
        selesai: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400',
        dibatalkan: 'bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400',
    }[status] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
}

async function loadStats() {
    try {
        const { data } = await api.get('/api/dashboard/stats');
        totals.value = data.totals;
    } finally {
        loadingStats.value = false;
    }
}

async function loadActivity() {
    try {
        const { data } = await api.get('/api/dashboard/recent-activity');
        recentActivity.value = data.data;
    } finally {
        loadingActivity.value = false;
    }
}

async function loadCharts() {
    const [transaksiRes, kategoriRes] = await Promise.all([
        api.get('/api/dashboard/transaksi-per-hari'),
        api.get('/api/dashboard/barang-per-kategori'),
    ]);

    transaksiChartData.value = {
        labels: transaksiRes.data.labels.map(formatDateLabel),
        datasets: [{
            label: 'Transaksi',
            data: transaksiRes.data.values,
            borderColor: '#4f46e5',
            backgroundColor: (ctx) => {
                const { chartArea, ctx: c } = ctx.chart;
                if (!chartArea) return 'rgba(79,70,229,0.15)';
                const gradient = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, 'rgba(79,70,229,0.3)');
                gradient.addColorStop(1, 'rgba(79,70,229,0)');
                return gradient;
            },
            tension: 0.4,
            fill: true,
            borderWidth: 2.5,
            pointRadius: 3,
            pointBackgroundColor: '#4f46e5',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverRadius: 6,
        }],
    };

    kategoriChartData.value = {
        labels: kategoriRes.data.labels,
        datasets: [{
            data: kategoriRes.data.values,
            backgroundColor: KATEGORI_COLORS,
            borderWidth: 3,
            borderColor: '#fff',
            hoverOffset: 10,
        }],
    };
}

async function refreshAll() {
    refreshing.value = true;
    await Promise.all([loadStats(), loadCharts(), loadActivity()]);
    refreshing.value = false;
}

const quickActions = [
    { label: 'Tambah Barang', icon: 'plus', action: () => router.push({ name: 'barang.index', query: { new: '1' } }) },
    { label: 'Kelola Kategori', icon: 'tag', action: () => router.push({ name: 'kategori.index' }) },
    { label: 'Kelola Pengguna', icon: 'users', action: () => router.push({ name: 'user.index' }) },
    { label: 'Refresh Data', icon: 'refresh', action: refreshAll, spin: true },
];

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 11) return 'Selamat pagi';
    if (h < 15) return 'Selamat siang';
    if (h < 19) return 'Selamat sore';
    return 'Selamat malam';
});
const firstName = computed(() => auth.user?.name?.split(' ')[0] ?? 'Admin');
const todayLabel = computed(() =>
    new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
);

const statCards = computed(() => [
    { label: 'Total Pengguna', value: totals.value.users, icon: 'users', color: 'indigo', to: { name: 'user.index' } },
    { label: 'Total Barang', value: totals.value.barang, icon: 'box', color: 'violet', to: { name: 'barang.index' } },
    { label: 'Barang Tersedia', value: totals.value.barang_tersedia, icon: 'check-circle', color: 'emerald', to: { name: 'barang.index' } },
    { label: 'Total Kategori', value: totals.value.kategori, icon: 'tag', color: 'sky', to: { name: 'kategori.index' } },
    { label: 'Total Transaksi', value: totals.value.transaksi, icon: 'swap', color: 'amber', to: { name: 'transaksi.index' } },
    { label: 'Total Ulasan', value: totals.value.ulasan, icon: 'star', color: 'rose', to: { name: 'ulasan.index' } },
]);

const topKategori = computed(() => {
    const labels = kategoriChartData.value.labels ?? [];
    const values = kategoriChartData.value.datasets?.[0]?.data ?? [];
    const max = Math.max(...values, 1);
    return labels.map((label, i) => ({
        label,
        value: values[i],
        pct: Math.round((values[i] / max) * 100),
        color: KATEGORI_COLORS[i % KATEGORI_COLORS.length],
    }));
});

let pollInterval = null;

onMounted(() => {
    loadStats();
    loadCharts();
    loadActivity();
    // Polling ringan setiap 30 detik agar dashboard terasa "hidup" tanpa perlu WebSocket.
    // Untuk update sungguh-sungguh real-time, ganti dengan Laravel Reverb + Echo.
    pollInterval = setInterval(() => { loadStats(); loadActivity(); }, 30000);
});
onUnmounted(() => clearInterval(pollInterval));
</script>

<template>
    <AppLayout>
        <!-- Header sambutan -->
        <div class="mb-8 flex animate-fade-in-up flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm capitalize text-slate-500 dark:text-slate-400">{{ todayLabel }}</p>
                <h1 class="mt-1 flex items-center gap-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                    {{ greeting }}, <span class="text-gradient-brand">{{ firstName }}</span>
                    <Icon name="sparkles" class="h-6 w-6 animate-float text-amber-400" />
                </h1>
                <p class="mt-1.5 flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-pulse-ring rounded-full bg-emerald-400"></span>
                        <span class="relative h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    Data diperbarui otomatis setiap 30 detik
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    v-for="qa in quickActions" :key="qa.label" @click="qa.action"
                    class="group flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-slate-900/5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md active:scale-95 dark:bg-slate-900 dark:text-slate-200 dark:ring-white/10 dark:hover:bg-slate-800"
                >
                    <Icon
                        :name="qa.icon" class="h-4 w-4 text-indigo-600 transition-transform duration-500 group-hover:scale-110 dark:text-indigo-400"
                        :class="{ 'animate-spin': qa.spin && refreshing }"
                    />
                    <span class="hidden sm:inline">{{ qa.label }}</span>
                </button>
            </div>
        </div>

        <!-- Kartu statistik -->
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-6">
            <StatCard
                v-for="(s, i) in statCards" :key="s.label"
                :label="s.label" :value="s.value" :icon="s.icon" :color="s.color" :to="s.to"
                :loading="loadingStats" :delay="i * 70"
            />
        </div>

        <!-- Grafik -->
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="animate-fade-in-up rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 lg:col-span-2 dark:bg-slate-900 dark:ring-white/10" style="animation-delay: 250ms">
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Transaksi 30 Hari Terakhir</h3>
                    <Icon name="trending-up" class="h-4 w-4 text-slate-300 dark:text-slate-600" />
                </div>
                <div class="h-72">
                    <Line v-if="transaksiChartData.labels.length" :data="transaksiChartData" :options="lineChartOptions" />
                    <EmptyState v-else icon="swap" title="Belum ada data transaksi" description="Grafik akan muncul begitu ada transaksi tercatat." />
                </div>
            </div>

            <div class="animate-fade-in-up rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10" style="animation-delay: 320ms">
                <h3 class="mb-1 text-sm font-semibold text-slate-700 dark:text-slate-200">Barang per Kategori</h3>
                <div v-if="kategoriChartData.labels.length">
                    <div class="mx-auto h-40 w-40">
                        <Doughnut :data="kategoriChartData" :options="doughnutChartOptions" />
                    </div>
                    <div class="mt-4 space-y-2.5">
                        <div v-for="k in topKategori" :key="k.label">
                            <div class="mb-1 flex items-start justify-between gap-2 text-xs">
                                <span class="flex min-w-0 items-center gap-1.5 break-words text-slate-600 dark:text-slate-300">
                                    <span class="h-2 w-2 shrink-0 rounded-full" :style="{ backgroundColor: k.color }"></span>
                                    {{ k.label }}
                                </span>
                                <span class="shrink-0 font-medium text-slate-500 dark:text-slate-400">{{ k.value }}</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                                <div class="h-full rounded-full transition-all duration-700 ease-out" :style="{ width: k.pct + '%', backgroundColor: k.color }"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <EmptyState v-else icon="tag" title="Belum ada kategori" />
            </div>
        </div>

        <!-- Aktivitas terbaru -->
        <div class="animate-fade-in-up rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10" style="animation-delay: 380ms">
            <h3 class="mb-1 text-sm font-semibold text-slate-700 dark:text-slate-200">Aktivitas Terbaru</h3>

            <div v-if="loadingActivity" class="divide-y divide-slate-100 dark:divide-slate-800">
                <div v-for="i in 4" :key="i" class="flex items-center gap-3 py-3">
                    <div class="skeleton h-9 w-9 rounded-full"></div>
                    <div class="flex-1 space-y-2">
                        <div class="skeleton h-3.5 w-1/3 rounded"></div>
                        <div class="skeleton h-3 w-1/4 rounded"></div>
                    </div>
                </div>
            </div>

            <EmptyState v-else-if="!recentActivity.length" icon="inbox" title="Belum ada aktivitas" description="Transaksi dan ulasan terbaru akan tampil di sini." />

            <ul v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                <li
                    v-for="(a, i) in recentActivity" :key="`${a.type}-${a.id}`"
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
                        <p class="truncate text-xs text-slate-400 dark:text-slate-500">{{ a.subtitle }} · {{ timeAgo(a.at) }}</p>
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
