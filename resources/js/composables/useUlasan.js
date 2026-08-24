import { ref, reactive, watch } from 'vue';
import api from '../lib/axios';

// Sama seperti Transaksi: paginator mentah, meta FLAT. Ulasan bersifat moderasi
// (lihat + hapus) karena secara alami dibuat oleh pengguna, bukan diketik admin.
export function useUlasan() {
    const items = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const meta = reactive({ current_page: 1, last_page: 1, total: 0 });
    const filters = reactive({ barang_id: '', page: 1 });

    async function fetchUlasan() {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await api.get('/api/ulasan', { params: { ...filters } });
            items.value = data.data;
            meta.current_page = data.current_page;
            meta.last_page = data.last_page;
            meta.total = data.total;
        } catch {
            error.value = 'Gagal memuat data ulasan.';
        } finally {
            loading.value = false;
        }
    }

    watch(() => filters.barang_id, () => {
        filters.page = 1;
        fetchUlasan();
    });

    function goToPage(page) {
        if (page < 1 || page > meta.last_page) return;
        filters.page = page;
        fetchUlasan();
    }

    async function deleteUlasan(id) {
        await api.delete(`/api/ulasan/${id}`);
        items.value = items.value.filter((u) => u.id !== id);
    }

    return { items, loading, error, meta, filters, fetchUlasan, goToPage, deleteUlasan };
}
