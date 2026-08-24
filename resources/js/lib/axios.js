import axios from 'axios';

const api = axios.create({
    baseURL: '/',
    withCredentials: true, // wajib: kirim cookie session Sanctum
    withXSRFToken: true,   // axios otomatis kirim header X-XSRF-TOKEN dari cookie
    headers: {
        Accept: 'application/json',
    },
});

let csrfReady = false;

/**
 * Sanctum SPA butuh cookie CSRF di-set sekali sebelum request "unsafe"
 * (POST/PUT/PATCH/DELETE) pertama, termasuk sebelum login.
 */
export async function ensureCsrfCookie() {
    if (csrfReady) return;
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    csrfReady = true;
}

api.interceptors.request.use(async (config) => {
    const unsafeMethods = ['post', 'put', 'patch', 'delete'];
    if (unsafeMethods.includes(config.method)) {
        await ensureCsrfCookie();
    }
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Session habis / belum login → redirect ke halaman login
            const authStore = window.__piniaAuthStore;
            authStore?.forceLogout?.();
        }
        return Promise.reject(error);
    }
);

export default api;
