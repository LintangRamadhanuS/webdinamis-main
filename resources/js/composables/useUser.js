import { ref, reactive, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import api from '../lib/axios';

// Endpoint singular: /api/user (lihat routes/api.php -> Route::apiResource('user', ...)).
// Paginator mentah juga, sama seperti Transaksi/Ulasan.
export function useUser() {
    const items = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const meta = reactive({ current_page: 1, last_page: 1, total: 0 });
    const filters = reactive({ q: '', page: 1 });

    async function fetchUsers() {
        loading.value = true;
        error.value = null;
        try {
            const { data } = await api.get('/api/user', { params: { ...filters } });
            items.value = data.data;
            meta.current_page = data.current_page;
            meta.last_page = data.last_page;
            meta.total = data.total;
        } catch {
            error.value = 'Gagal memuat data pengguna.';
        } finally {
            loading.value = false;
        }
    }

    const debouncedFetch = useDebounceFn(() => {
        filters.page = 1;
        fetchUsers();
    }, 400);
    watch(() => filters.q, debouncedFetch);

    function goToPage(page) {
        if (page < 1 || page > meta.last_page) return;
        filters.page = page;
        fetchUsers();
    }

    async function createUser(payload) {
        const { data } = await api.post('/api/user', payload);
        return data;
    }

    async function updateUser(id, payload) {
        const { data } = await api.put(`/api/user/${id}`, payload);
        return data;
    }

    async function deleteUser(id) {
        await api.delete(`/api/user/${id}`);
        items.value = items.value.filter((u) => u.id !== id);
    }

    return {
        items, loading, error, meta, filters,
        fetchUsers, goToPage, createUser, updateUser, deleteUser,
    };
}
