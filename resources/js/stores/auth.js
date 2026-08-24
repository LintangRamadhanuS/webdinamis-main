import { defineStore } from 'pinia';
import api, { ensureCsrfCookie } from '../lib/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        checked: false, // sudah pernah cek session belum saat app load
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,
        isAdmin: (state) => state.user?.role === 'admin',
    },

    actions: {
        async login(email, password) {
            this.loading = true;
            try {
                await ensureCsrfCookie();
                const { data } = await api.post('/api/login', { email, password });
                this.user = data.user;
                return { success: true };
            } catch (error) {
                const message =
                    error.response?.data?.errors?.email?.[0] ??
                    error.response?.data?.message ??
                    'Login gagal, silakan coba lagi.';
                return { success: false, message };
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await api.post('/api/logout');
            } finally {
                this.user = null;
            }
        },

        forceLogout() {
            this.user = null;
        },

        /** Dipanggil setelah update profil supaya sidebar dsb langsung menampilkan data terbaru. */
        setUser(user) {
            this.user = user;
        },

        /** Dipanggil sekali saat app pertama kali load, untuk restore session. */
        async fetchUser() {
            try {
                const { data } = await api.get('/api/me');
                this.user = data.user;
            } catch {
                this.user = null;
            } finally {
                this.checked = true;
            }
        },
    },
});
