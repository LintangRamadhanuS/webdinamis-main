import { ref, reactive, watch } from 'vue';
import api from '../lib/axios';

// Catatan: GET /api/transaksi mengembalikan paginator Laravel mentah (bukan API Resource),
// jadi field meta-nya FLAT (data.current_page, bukan data.meta.current_page).
export function useTransaksi() {
    const items = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const meta = reactive({ current_page: 1, last_page: 1, total: 0 });
    const filters = reactive({ status: '', page: 1 });

    async function fetchTransaksi() {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await api.get('/api/transaksi', { params: { ...filters } });
            items.value = data.data;
            meta.current_page = data.current_page;
            meta.last_page = data.last_page;
            meta.total = data.total;
        } catch {
            error.value = 'Gagal memuat data transaksi.';
        } finally {
            loading.value = false;
        }
    }

    watch(() => filters.status, () => {
        filters.page = 1;
        fetchTransaksi();
    });

    function goToPage(page) {
        if (page < 1 || page > meta.last_page) return;
        filters.page = page;
        fetchTransaksi();
    }

    async function createTransaksi(payload) {
        const { data } = await api.post('/api/transaksi', payload);
        return data;
    }

    async function updateTransaksi(id, payload) {
        const { data } = await api.put(`/api/transaksi/${id}`, payload);
        return data;
    }

    async function deleteTransaksi(id) {
        await api.delete(`/api/transaksi/${id}`);
        items.value = items.value.filter((t) => t.id !== id);
    }

    async function batalkanTransaksi(id) {
        const { data } = await api.post(`/api/transaksi/${id}/batalkan`);
        return data;
    }

    return {
        items, loading, error, meta, filters,
        fetchTransaksi, goToPage, createTransaksi, updateTransaksi, deleteTransaksi, batalkanTransaksi,
    };
}
