import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../pages/auth/Login.vue'),
        meta: { guestOnly: true, title: 'Masuk' },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('../pages/auth/ForgotPassword.vue'),
        meta: { title: 'Lupa Password' },
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('../pages/auth/ResetPassword.vue'),
        meta: { title: 'Reset Password' },
    },
    {
        // Tidak pernah benar-benar dirender -- beforeEach selalu redirect dari sini
        // ke /dashboard (admin) atau /beranda (pengguna) begitu role diketahui.
        path: '/',
        name: 'home',
        meta: { requiresAuth: true },
    },

    // --- Halaman admin (pengelola platform) ---
    {
        path: '/dashboard',
        name: 'dashboard',
        component: () => import('../pages/dashboard/Dashboard.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Dashboard' },
    },
    {
        path: '/barang',
        name: 'barang.index',
        component: () => import('../pages/barang/BarangIndex.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Barang' },
    },
    {
        path: '/kategori',
        name: 'kategori.index',
        component: () => import('../pages/kategori/KategoriIndex.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Kategori' },
    },
    {
        path: '/transaksi',
        name: 'transaksi.index',
        component: () => import('../pages/transaksi/TransaksiIndex.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Transaksi' },
    },
    {
        path: '/ulasan',
        name: 'ulasan.index',
        component: () => import('../pages/ulasan/UlasanIndex.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Ulasan' },
    },
    {
        path: '/users',
        name: 'user.index',
        component: () => import('../pages/user/UserIndex.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Pengguna' },
    },

    // --- Halaman pengguna biasa (mahasiswa) — hanya melihat miliknya sendiri ---
    {
        path: '/beranda',
        name: 'beranda',
        component: () => import('../pages/dashboard/MyDashboard.vue'),
        meta: { requiresAuth: true, title: 'Beranda' },
    },
    {
        path: '/jelajah',
        name: 'katalog.index',
        component: () => import('../pages/katalog/KatalogIndex.vue'),
        meta: { requiresAuth: true, title: 'Jelajah Barang' },
    },
    {
        path: '/transaksi-saya',
        name: 'transaksi.saya',
        component: () => import('../pages/transaksi/MyTransaksi.vue'),
        meta: { requiresAuth: true, title: 'Transaksi Saya' },
    },
    {
        path: '/ulasan-saya',
        name: 'ulasan.saya',
        component: () => import('../pages/ulasan/MyUlasan.vue'),
        meta: { requiresAuth: true, title: 'Ulasan Saya' },
    },

    // --- Bersama (admin & pengguna) ---
    {
        path: '/profile',
        name: 'profile',
        component: () => import('../pages/profile/ProfileIndex.vue'),
        meta: { requiresAuth: true, title: 'Profil Saya' },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!auth.checked) {
        await auth.fetchUser();
    }

    // '/' selalu diarahkan sesuai role, begitu diketahui.
    if (to.name === 'home') {
        if (!auth.isAuthenticated) return { name: 'login' };
        return auth.isAdmin ? { name: 'dashboard' } : { name: 'beranda' };
    }

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    // Pengguna biasa yang mencoba membuka halaman admin -> lempar ke beranda mereka.
    if (to.meta.requiresAdmin && !auth.isAdmin) {
        return { name: 'beranda' };
    }

    if (to.meta.guestOnly && auth.isAuthenticated) {
        return auth.isAdmin ? { name: 'dashboard' } : { name: 'beranda' };
    }

    return true;
});

router.afterEach((to) => {
    document.title = to.meta.title ? `${to.meta.title} — Bekas Admin` : 'Bekas Admin';
});

export default router;
