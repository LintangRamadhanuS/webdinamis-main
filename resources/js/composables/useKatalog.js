import { ref, reactive, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import api from '../lib/axios';

// Katalog untuk halaman "Jelajah Barang" -- selalu hanya menampilkan barang berstatus
// tersedia. Endpoint sama dengan /api/barang milik admin (Resource-wrapped, meta bersarang),
// makanya bentuk meta di sini sama dengan useBarang.js.
export function useKatalog() {
    const items = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const meta = reactive({ current_page: 1, last_page: 1, total: 0 });
    const filters = reactive({ q: '', kategori_id: '', page: 1 });

    async function fetchKatalog() {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await api.get('/api/barang', {
                params: { ...filters, status: 'tersedia', per_page: 12 },
            });
            items.value = data.data;
            meta.current_page = data.meta.current_page;
            meta.last_page = data.meta.last_page;
            meta.total = data.meta.total;
        } catch {
            error.value = 'Gagal memuat katalog barang.';
        } finally {
            loading.value = false;
        }
    }

    const debouncedFetch = useDebounceFn(() => {
        filters.page = 1;
        fetchKatalog();
    }, 400);
    watch(() => filters.q, debouncedFetch);
    watch(() => filters.kategori_id, () => {
        filters.page = 1;
        fetchKatalog();
    });

    function goToPage(page) {
        if (page < 1 || page > meta.last_page) return;
        filters.page = page;
        fetchKatalog();
    }

    async function beli(barangId) {
        const { data } = await api.post(`/api/barang/${barangId}/beli`);
        return data;
    }

    return { items, loading, error, meta, filters, fetchKatalog, goToPage, beli };
}
