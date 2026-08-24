import { ref, reactive, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import api from '../lib/axios';

export function useBarang() {
    const items = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const meta = reactive({ current_page: 1, last_page: 1, total: 0 });

    const filters = reactive({
        q: '',
        kategori_id: '',
        status: '',
        kondisi: '',
        sort: 'terbaru',
        page: 1,
    });

    // Simpan token pembatalan supaya request lama tidak "menang" atas request baru
    // (contoh klasik: race condition saat user ngetik cepat di search box)
    let abortController = null;

    async function fetchBarang() {
        loading.value = true;
        error.value = null;

        abortController?.abort();
        abortController = new AbortController();

        try {
            const { data } = await api.get('/api/barang', {
                params: { ...filters },
                signal: abortController.signal,
            });

            items.value = data.data;
            meta.current_page = data.meta.current_page;
            meta.last_page = data.meta.last_page;
            meta.total = data.meta.total;
        } catch (err) {
            if (err.name !== 'CanceledError') {
                error.value = 'Gagal memuat data barang. Periksa koneksi Anda.';
            }
        } finally {
            loading.value = false;
        }
    }

    // Search diketik → tunggu 400ms jeda sebelum request (hemat request, terasa instan)
    const debouncedFetch = useDebounceFn(() => {
        filters.page = 1;
        fetchBarang();
    }, 400);

    watch(() => filters.q, debouncedFetch);
    watch([() => filters.kategori_id, () => filters.status, () => filters.kondisi, () => filters.sort], () => {
        filters.page = 1;
        fetchBarang();
    });

    function goToPage(page) {
        if (page < 1 || page > meta.last_page) return;
        filters.page = page;
        fetchBarang();
    }

    async function createBarang(formData) {
        const { data } = await api.post('/api/barang', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return data.data;
    }

    async function updateBarang(id, formData) {
        formData.append('_method', 'PUT'); // Laravel method-spoofing utk multipart
        const { data } = await api.post(`/api/barang/${id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        return data.data;
    }

    async function deleteBarang(id) {
        await api.delete(`/api/barang/${id}`);
        items.value = items.value.filter((b) => b.id !== id); // update UI instan, tanpa fetch ulang
    }

    return {
        items, loading, error, meta, filters,
        fetchBarang, goToPage, createBarang, updateBarang, deleteBarang,
    };
}
