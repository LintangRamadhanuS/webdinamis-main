<script setup>
import { reactive, computed, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import Icon from '../../components/ui/Icon.vue';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const form = reactive({ email: '', password: '' });
const serverError = reactive({ message: '' });
const showPassword = ref(false);

// Validasi real-time di client (mirror rule server) — feedback instan tanpa nunggu submit
const errors = computed(() => {
    const e = {};
    if (form.email && !/^\S+@\S+\.\S+$/.test(form.email)) {
        e.email = 'Format email tidak valid.';
    }
    if (form.password && form.password.length < 6) {
        e.password = 'Password minimal 6 karakter.';
    }
    return e;
});

const isValid = computed(() =>
    form.email && form.password && Object.keys(errors.value).length === 0
);

async function handleSubmit() {
    serverError.message = '';
    const result = await auth.login(form.email, form.password);

    if (result.success) {
        router.push(route.query.redirect || { name: 'dashboard' });
    } else {
        serverError.message = result.message;
    }
}

function fillDemoCredentials() {
    form.email = 'admin@bekas.test';
    form.password = 'password';
}
</script>

<template>
    <div class="relative flex min-h-screen overflow-hidden bg-slate-50 dark:bg-slate-950">
        <!-- Panel branding kiri (desktop) -->
        <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-gradient-to-br from-indigo-700 via-violet-700 to-fuchsia-700 p-12 text-white lg:flex">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-20 top-10 h-72 w-72 rounded-full bg-white/10 blur-3xl animate-blob"></div>
                <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-fuchsia-400/20 blur-3xl animate-blob" style="animation-delay: -6s"></div>
            </div>

            <div class="relative flex items-center gap-3 animate-fade-in-up">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-xl font-extrabold backdrop-blur-sm">B</div>
                <span class="text-lg font-bold">Bekas</span>
            </div>

            <div class="relative animate-fade-in-up" style="animation-delay: 120ms">
                <h1 class="text-4xl font-extrabold leading-tight">
                    Kelola marketplace<br />barang bekas kampusmu.
                </h1>
                <p class="mt-4 max-w-md text-sm text-indigo-100/90">
                    Satu panel untuk memantau pengguna, barang, kategori, transaksi, dan ulasan — semuanya real-time.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <div
                        v-for="(f, i) in [
                            { icon: 'users', label: 'Pengguna' },
                            { icon: 'box', label: 'Barang' },
                            { icon: 'tag', label: 'Kategori' },
                            { icon: 'swap', label: 'Transaksi' },
                            { icon: 'star', label: 'Ulasan' },
                        ]"
                        :key="f.label"
                        class="flex animate-float items-center gap-2 rounded-full bg-white/10 px-3.5 py-2 text-xs font-medium backdrop-blur-sm"
                        :style="{ animationDelay: `${i * 0.4}s` }"
                    >
                        <Icon :name="f.icon" class="h-3.5 w-3.5" /> {{ f.label }}
                    </div>
                </div>
            </div>

            <p class="relative animate-fade-in-up text-xs text-indigo-200/70" style="animation-delay: 200ms">
                © {{ new Date().getFullYear() }} Bekas — Marketplace Barang Bekas
            </p>
        </div>

        <!-- Panel form -->
        <div class="flex w-full items-center justify-center px-4 py-12 sm:px-6 lg:w-1/2">
            <div class="pointer-events-none absolute inset-0 lg:hidden">
                <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-indigo-200/30 blur-3xl animate-blob"></div>
            </div>

            <form @submit.prevent="handleSubmit" class="relative w-full max-w-sm animate-fade-in-up space-y-5 rounded-2xl bg-white p-8 shadow-xl shadow-slate-200/60 ring-1 ring-slate-900/5 dark:bg-slate-900 dark:shadow-none dark:ring-white/10">
                <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-base font-extrabold text-white lg:hidden">
                    B
                </div>

                <div>
                    <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Selamat datang kembali</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Masuk untuk mengelola panel admin Bekas.</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Email</label>
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                            <Icon name="mail" class="h-4 w-4" />
                        </span>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="nama@kampus.ac.id"
                            class="w-full rounded-lg border py-2 pl-10 pr-3 text-sm transition-colors focus:outline-none focus:ring-2 dark:bg-slate-800 dark:text-slate-100"
                            :class="errors.email ? 'border-rose-400 focus:ring-rose-200 dark:border-rose-500' : 'border-slate-300 focus:ring-indigo-200 dark:border-slate-600 dark:focus:ring-indigo-500/30'"
                        />
                    </div>
                    <p v-if="errors.email" class="mt-1 text-xs text-rose-500">{{ errors.email }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Password</label>
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                            <Icon name="lock" class="h-4 w-4" />
                        </span>
                        <input
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••"
                            class="w-full rounded-lg border py-2 pl-10 pr-16 text-sm transition-colors focus:outline-none focus:ring-2 dark:bg-slate-800 dark:text-slate-100"
                            :class="errors.password ? 'border-rose-400 focus:ring-rose-200 dark:border-rose-500' : 'border-slate-300 focus:ring-indigo-200 dark:border-slate-600 dark:focus:ring-indigo-500/30'"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-xs font-medium text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                            {{ showPassword ? 'Sembunyikan' : 'Tampilkan' }}
                        </button>
                    </div>
                    <p v-if="errors.password" class="mt-1 text-xs text-rose-500">{{ errors.password }}</p>
                    <RouterLink :to="{ name: 'forgot-password' }" class="mt-1.5 inline-block text-xs font-medium text-indigo-500 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                        Lupa password?
                    </RouterLink>
                </div>

                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-1"
                    leave-active-class="transition duration-150 ease-in"
                    leave-to-class="opacity-0"
                >
                    <p v-if="serverError.message" class="flex items-center gap-2 rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                        <Icon name="alert" class="h-4 w-4 shrink-0" /> {{ serverError.message }}
                    </p>
                </Transition>

                <button
                    type="submit"
                    :disabled="!isValid || auth.loading"
                    class="flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition-all duration-200 hover:shadow-xl hover:shadow-indigo-300 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none dark:shadow-indigo-950 dark:hover:shadow-indigo-950"
                >
                    <span v-if="auth.loading" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    {{ auth.loading ? 'Memproses...' : 'Masuk' }}
                </button>

                <button
                    type="button"
                    @click="fillDemoCredentials"
                    class="w-full rounded-lg border border-dashed border-slate-300 py-2 text-xs font-medium text-slate-500 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-600 dark:text-slate-400 dark:hover:border-indigo-400 dark:hover:text-indigo-400"
                >
                    Gunakan kredensial demo (admin@bekas.test)
                </button>
            </form>
        </div>
    </div>
</template>
