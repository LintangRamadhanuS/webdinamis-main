<script setup>
import { computed, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useTheme } from '../composables/useTheme';
import Icon from '../components/ui/Icon.vue';

const auth = useAuthStore();
const router = useRouter();
const sidebarOpen = ref(false);
const { isDark, toggle: toggleTheme } = useTheme();

async function handleLogout() {
    await auth.logout();
    router.push({ name: 'login' });
}

const adminNavItems = [
    { name: 'dashboard', label: 'Dashboard', icon: 'home' },
    { name: 'barang.index', label: 'Barang', icon: 'box' },
    { name: 'kategori.index', label: 'Kategori', icon: 'tag' },
    { name: 'transaksi.index', label: 'Transaksi', icon: 'swap' },
    { name: 'ulasan.index', label: 'Ulasan', icon: 'star' },
    { name: 'user.index', label: 'Pengguna', icon: 'users' },
];
const penggunaNavItems = [
    { name: 'beranda', label: 'Beranda', icon: 'home' },
    { name: 'katalog.index', label: 'Jelajah Barang', icon: 'box' },
    { name: 'transaksi.saya', label: 'Transaksi Saya', icon: 'swap' },
    { name: 'ulasan.saya', label: 'Ulasan Saya', icon: 'star' },
];
const navItems = computed(() => (auth.isAdmin ? adminNavItems : penggunaNavItems));

const initials = computed(() => {
    const name = auth.user?.name?.trim();
    if (!name) return auth.user?.email?.[0]?.toUpperCase() ?? '?';
    const parts = name.split(/\s+/);
    return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase();
});
</script>

<template>
    <div class="relative flex min-h-screen bg-slate-50 dark:bg-slate-950">
        <!-- Blob dekoratif — sangat samar, tidak mengganggu keterbacaan konten. -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-indigo-200/30 blur-3xl animate-blob dark:bg-indigo-500/10"></div>
            <div class="absolute right-0 top-1/3 h-96 w-96 rounded-full bg-fuchsia-200/20 blur-3xl animate-blob dark:bg-fuchsia-500/10" style="animation-delay: -8s"></div>
        </div>

        <!-- Overlay mobile -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0" leave-to-class="opacity-0"
        >
            <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>
        </Transition>

        <!-- Sidebar: didok permanen di desktop, laci geser (CSS transform) di mobile -->
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-72 shrink-0 -translate-x-full flex-col bg-slate-900 text-slate-200 transition-transform duration-300 ease-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : ''"
        >
            <div class="relative overflow-hidden border-b border-white/10 px-6 py-5">
                <div class="absolute -right-6 -top-10 h-28 w-28 rounded-full bg-gradient-to-br from-indigo-500/30 to-fuchsia-500/20 blur-2xl"></div>
                <div class="relative flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-lg font-extrabold text-white shadow-lg shadow-indigo-900/40">
                        B
                    </div>
                    <div>
                        <p class="text-base font-bold leading-tight text-white">Bekas</p>
                        <p class="text-xs text-slate-400">{{ auth.isAdmin ? 'Admin Panel' : 'Panel Pengguna' }}</p>
                    </div>
                </div>
                <button
                    type="button"
                    class="absolute right-4 top-4 text-slate-400 hover:text-white lg:hidden"
                    aria-label="Tutup menu"
                    @click="sidebarOpen = false"
                >
                    <Icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
                <RouterLink
                    v-for="(item, i) in navItems"
                    :key="item.name"
                    :to="{ name: item.name }"
                    custom
                    v-slot="{ href, navigate, isActive }"
                >
                    <a
                        :href="href"
                        @click="(e) => { navigate(e); sidebarOpen = false; }"
                        class="group flex animate-fade-in-up items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-300"
                        :style="{ animationDelay: `${i * 60}ms` }"
                        :class="isActive
                            ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-900/30'
                            : 'text-slate-400 hover:bg-white/5 hover:text-white'"
                    >
                        <Icon
                            :name="item.icon"
                            class="h-[18px] w-[18px] shrink-0 transition-transform duration-300"
                            :class="isActive ? 'scale-110' : 'group-hover:scale-110'"
                        />
                        {{ item.label }}
                        <span v-if="isActive" class="ml-auto h-1.5 w-1.5 rounded-full bg-white"></span>
                    </a>
                </RouterLink>
            </nav>

            <div class="border-t border-white/10 px-4 py-4">
                <RouterLink :to="{ name: 'profile' }" @click="sidebarOpen = false" class="flex items-center gap-3 rounded-xl px-2 py-2 transition-colors hover:bg-white/5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-xs font-bold text-white">
                        {{ initials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <p class="truncate text-sm font-medium text-white">{{ auth.user?.name ?? 'Pengguna' }}</p>
                            <span
                                class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide"
                                :class="auth.isAdmin ? 'bg-indigo-500/20 text-indigo-300' : 'bg-slate-500/20 text-slate-300'"
                            >
                                {{ auth.isAdmin ? 'Admin' : 'Pengguna' }}
                            </span>
                        </div>
                        <p class="truncate text-xs text-slate-400">{{ auth.user?.email }}</p>
                    </div>
                </RouterLink>
                <button
                    @click="toggleTheme"
                    class="mt-1 flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm text-slate-400 transition-colors hover:bg-white/5 hover:text-white"
                >
                    <span class="flex items-center gap-2">
                        <Icon :name="isDark ? 'moon' : 'sun'" class="h-4 w-4" />
                        Mode Gelap
                    </span>
                    <span
                        class="relative h-5 w-9 shrink-0 rounded-full transition-colors duration-200"
                        :class="isDark ? 'bg-indigo-500' : 'bg-slate-600'"
                    >
                        <span
                            class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform duration-200"
                            :class="isDark ? 'translate-x-4' : 'translate-x-0'"
                        ></span>
                    </span>
                </button>
                <button
                    @click="handleLogout"
                    class="mt-1 flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm text-rose-400 transition-colors hover:bg-rose-500/10 hover:text-rose-300"
                >
                    <Icon name="logout" class="h-4 w-4" /> Keluar
                </button>
            </div>
        </aside>

        <!-- Konten -->
        <div class="relative flex min-w-0 flex-1 flex-col lg:pl-72">
            <!-- Topbar (mobile only) -->
            <header class="glass sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200/70 px-4 py-3 dark:border-slate-800/70 lg:hidden">
                <button type="button" class="text-slate-600 dark:text-slate-300" aria-label="Buka menu" @click="sidebarOpen = true">
                    <Icon name="menu" class="h-6 w-6" />
                </button>
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-xs font-extrabold text-white">
                    B
                </div>
                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ auth.isAdmin ? 'Bekas Admin' : 'Bekas' }}</span>
            </header>

            <main class="relative z-10 flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
