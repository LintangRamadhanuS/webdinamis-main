import { ref } from 'vue';
import api from '../lib/axios';

// Catatan: GET /api/kategori TIDAK dipaginasi di backend (mengembalikan array biasa),
// jadi composable ini sengaja tanpa meta/filters seperti useBarang.
export function useKategori() {
    const items = ref([]);
    const loading = ref(false);
    const error = ref(null);

    async function fetchKategori() {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await api.get('/api/kategori');
            items.value = data;
        } catch {
            error.value = 'Gagal memuat data kategori.';
        } finally {
            loading.value = false;
        }
    }

    async function createKategori(payload) {
        const { data } = await api.post('/api/kategori', payload);
        return data;
    }

    async function updateKategori(id, payload) {
        const { data } = await api.put(`/api/kategori/${id}`, payload);
        return data;
    }

    async function deleteKategori(id) {
        await api.delete(`/api/kategori/${id}`);
        items.value = items.value.filter((k) => k.id !== id);
    }

    return { items, loading, error, fetchKategori, createKategori, updateKategori, deleteKategori };
}
