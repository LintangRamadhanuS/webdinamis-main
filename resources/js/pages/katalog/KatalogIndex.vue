<script setup>
import { onMounted, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import EmptyState from '../../components/ui/EmptyState.vue';
import Pagination from '../../components/ui/Pagination.vue';
import Icon from '../../components/ui/Icon.vue';
import BarangDetailModal from '../../components/katalog/BarangDetailModal.vue';
import { useKatalog } from '../../composables/useKatalog';
import { useToast } from '../../composables/useToast';
import api from '../../lib/axios';

const { items, loading, error, meta, filters, fetchKatalog, goToPage, beli } = useKatalog();
const toast = useToast();

const kategoris = ref([]);
const selectedBarang = ref(null);
const showDetail = ref(false);
const buying = ref(false);

onMounted(async () => {
    fetchKatalog();
    try {
        const { data } = await api.get('/api/kategori');
        kategoris.value = data;
    } catch {
        toast.error('Gagal memuat daftar kategori.');
    }
});

function openDetail(barang) {
    selectedBarang.value = barang;
    showDetail.value = true;
}

async function handleBeli(barangId) {
    buying.value = true;
    try {
        await beli(barangId);
        toast.success('Permintaan beli terkirim! Pantau statusnya di halaman Transaksi Saya.');
        showDetail.value = false;
        fetchKatalog(); // barang yang baru dibeli akan hilang dari daftar "tersedia"
    } catch (err) {
        toast.error(err.response?.data?.message ?? 'Gagal memproses pembelian.');
    } finally {
        buying.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <PageHeader title="Jelajah Barang" subtitle="Temukan barang bekas yang kamu butuhkan." />

        <div class="mb-6 grid animate-fade-in-up grid-cols-1 gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-3 dark:bg-slate-900 dark:ring-white/10" style="animation-delay: 80ms">
            <div class="relative sm:col-span-2">
                <Icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                <input
                    v-model="filters.q" type="text" placeholder="Cari nama barang..."
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
        </div>

        <p v-if="error" class="mb-4 flex items-center gap-2 rounded-lg bg-rose-50 px-4 py-2.5 text-sm text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
            <Icon name="alert" class="h-4 w-4 shrink-0" /> {{ error }}
        </p>

        <div v-if="loading" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <div v-for="i in 8" :key="i" class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10">
                <div class="skeleton h-40 w-full"></div>
                <div class="p-4">
                    <div class="skeleton h-3 w-1/3 rounded"></div>
                    <div class="skeleton mt-2 h-4 w-2/3 rounded"></div>
                    <div class="skeleton mt-2 h-4 w-1/2 rounded"></div>
                </div>
            </div>
        </div>

        <div v-else-if="!items.length" class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10">
            <EmptyState icon="box" title="Tidak ada barang yang cocok" description="Coba ubah kata kunci pencarian atau kategori yang dipilih." />
        </div>

        <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <div
                v-for="(b, i) in items" :key="b.id" @click="openDetail(b)"
                class="group animate-fade-in-up cursor-pointer overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 lift-on-hover dark:bg-slate-900 dark:ring-white/10"
                :style="{ animationDelay: `${i * 50}ms` }"
            >
                <div class="flex h-36 items-center justify-center overflow-hidden bg-slate-100 sm:h-40 dark:bg-slate-800">
                    <img v-if="b.foto_url" :src="b.foto_url" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" :alt="b.nama" />
                    <Icon v-else name="image" class="h-10 w-10 text-slate-300 dark:text-slate-600" />
                </div>
                <div class="p-4">
                    <span class="text-xs font-medium text-indigo-500 dark:text-indigo-400">{{ b.kategori?.nama_kategori ?? '-' }}</span>
                    <h3 class="mt-1 truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ b.nama }}</h3>
                    <p class="mt-1 font-bold text-slate-900 dark:text-white">{{ b.harga_format }}</p>
                    <span class="mt-2 inline-block rounded-full bg-slate-100 px-2 py-0.5 text-xs capitalize text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ b.kondisi }}</span>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <Pagination :current-page="meta.current_page" :last-page="meta.last_page" @change="goToPage" />
        </div>

        <BarangDetailModal
            :show="showDetail" :barang="selectedBarang" :buying="buying"
            @close="showDetail = false" @beli="handleBeli"
        />
    </AppLayout>
</template>
