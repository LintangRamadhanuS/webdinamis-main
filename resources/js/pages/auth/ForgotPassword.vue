<script setup>
import { ref } from 'vue';
import { RouterLink } from 'vue-router';
import Icon from '../../components/ui/Icon.vue';
import api from '../../lib/axios';

const email = ref('');
const loading = ref(false);
const sent = ref(false);
const errorMsg = ref('');

async function handleSubmit() {
    loading.value = true;
    errorMsg.value = '';
    try {
        await api.post('/api/forgot-password', {
            email: email.value,
            // Kirim origin browser yang sebenarnya (termasuk port) supaya link di email
            // tidak salah arah kalau APP_URL di .env server tidak persis sama. Backend
            // cuma memakainya kalau host-nya sudah terdaftar di SANCTUM_STATEFUL_DOMAINS.
            frontend_url: window.location.origin,
        });
        sent.value = true;
    } catch (err) {
        errorMsg.value = err.response?.data?.errors?.email?.[0] ?? 'Terjadi kesalahan, coba lagi.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-50 px-4 py-12 dark:bg-slate-950">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-indigo-200/30 blur-3xl animate-blob dark:bg-indigo-500/10"></div>
            <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-fuchsia-200/20 blur-3xl animate-blob dark:bg-fuchsia-500/10" style="animation-delay: -8s"></div>
        </div>

        <div class="relative w-full max-w-sm animate-fade-in-up rounded-2xl bg-white p-8 shadow-xl shadow-slate-200/60 ring-1 ring-slate-900/5 dark:bg-slate-900 dark:shadow-none dark:ring-white/10">
            <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-base font-extrabold text-white">
                B
            </div>

            <template v-if="!sent">
                <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Lupa password?</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Masukkan email akunmu, kami kirimkan tautan untuk reset password.</p>

                <form @submit.prevent="handleSubmit" class="mt-5 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Email</label>
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                                <Icon name="mail" class="h-4 w-4" />
                            </span>
                            <input
                                v-model="email" type="email" required placeholder="nama@kampus.ac.id"
                                class="w-full rounded-lg border border-slate-300 py-2 pl-10 pr-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                            />
                        </div>
                        <p v-if="errorMsg" class="mt-1 text-xs text-rose-500">{{ errorMsg }}</p>
                    </div>

                    <button
                        type="submit" :disabled="loading"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition-all duration-200 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-indigo-950"
                    >
                        <span v-if="loading" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                        {{ loading ? 'Mengirim...' : 'Kirim Tautan Reset' }}
                    </button>
                </form>
            </template>

            <template v-else>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                    <Icon name="mail" class="h-6 w-6" />
                </div>
                <h1 class="mt-4 text-xl font-bold text-slate-800 dark:text-slate-100">Cek email kamu</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Jika <strong>{{ email }}</strong> terdaftar, tautan reset password sudah dikirim ke sana.
                </p>
                <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                    Mode pengembangan lokal: email tidak benar-benar terkirim kecuali SMTP sudah dikonfigurasi —
                    cek tautannya di <code>storage/logs/laravel.log</code>.
                </p>
            </template>

            <RouterLink :to="{ name: 'login' }" class="mt-6 flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                <Icon name="chevron-left" class="h-4 w-4" /> Kembali ke halaman masuk
            </RouterLink>
        </div>
    </div>
</template>
