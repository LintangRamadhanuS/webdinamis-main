<script setup>
import { reactive, computed, ref } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import Icon from '../../components/ui/Icon.vue';
import api from '../../lib/axios';

const route = useRoute();
const router = useRouter();

const form = reactive({ password: '', password_confirmation: '' });
const loading = ref(false);
const errorMsg = ref('');
const success = ref(false);

const tokenMissing = computed(() => !route.query.token || !route.query.email);

const passwordIssues = computed(() => {
    if (!form.password) return [];
    const pw = form.password;
    const issues = [];
    if (pw.length < 8) issues.push('minimal 8 karakter');
    if (!/[a-z]/.test(pw)) issues.push('huruf kecil');
    if (!/[A-Z]/.test(pw)) issues.push('huruf besar');
    if (!/[0-9]/.test(pw)) issues.push('angka');
    return issues;
});

const isValid = computed(() =>
    form.password && form.password === form.password_confirmation && !passwordIssues.value.length
);

async function handleSubmit() {
    if (!isValid.value) return;
    loading.value = true;
    errorMsg.value = '';
    try {
        await api.post('/api/reset-password', {
            token: route.query.token,
            email: route.query.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
        });
        success.value = true;
        setTimeout(() => router.push({ name: 'login' }), 2500);
    } catch (err) {
        errorMsg.value = err.response?.data?.errors?.email?.[0] ?? 'Tautan reset tidak valid atau sudah kedaluwarsa.';
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

            <template v-if="tokenMissing">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                    <Icon name="alert" class="h-6 w-6" />
                </div>
                <h1 class="mt-4 text-xl font-bold text-slate-800 dark:text-slate-100">Tautan tidak lengkap</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Buka lagi tautan reset dari email kamu, atau minta yang baru.</p>
            </template>

            <template v-else-if="success">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
                    <Icon name="check-circle" class="h-6 w-6" />
                </div>
                <h1 class="mt-4 text-xl font-bold text-slate-800 dark:text-slate-100">Password berhasil direset</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Mengarahkan ke halaman masuk...</p>
            </template>

            <template v-else>
                <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">Atur password baru</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Untuk akun {{ route.query.email }}</p>

                <form @submit.prevent="handleSubmit" class="mt-5 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Password Baru</label>
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                                <Icon name="lock" class="h-4 w-4" />
                            </span>
                            <input
                                v-model="form.password" type="password" placeholder="••••••••"
                                class="w-full rounded-lg border border-slate-300 py-2 pl-10 pr-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                            />
                        </div>
                        <p class="mt-1 text-xs" :class="passwordIssues.length ? 'text-rose-500' : 'text-slate-400 dark:text-slate-500'">
                            Wajib huruf besar, huruf kecil, angka, minimal 8 karakter.
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Konfirmasi Password</label>
                        <div class="relative mt-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 dark:text-slate-500">
                                <Icon name="lock" class="h-4 w-4" />
                            </span>
                            <input
                                v-model="form.password_confirmation" type="password" placeholder="••••••••"
                                class="w-full rounded-lg border py-2 pl-10 pr-3 text-sm transition-colors focus:outline-none focus:ring-2 dark:bg-slate-800 dark:text-slate-100"
                                :class="form.password_confirmation && form.password !== form.password_confirmation ? 'border-rose-400 focus:ring-rose-200 dark:border-rose-500' : 'border-slate-300 focus:ring-indigo-200 dark:border-slate-600 dark:focus:ring-indigo-500/30'"
                            />
                        </div>
                        <p v-if="form.password_confirmation && form.password !== form.password_confirmation" class="mt-1 text-xs text-rose-500">
                            Konfirmasi password tidak sama.
                        </p>
                    </div>

                    <p v-if="errorMsg" class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">{{ errorMsg }}</p>

                    <button
                        type="submit" :disabled="!isValid || loading"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition-all duration-200 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-indigo-950"
                    >
                        <span v-if="loading" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                        {{ loading ? 'Memproses...' : 'Reset Password' }}
                    </button>
                </form>
            </template>

            <RouterLink :to="{ name: 'login' }" class="mt-6 flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                <Icon name="chevron-left" class="h-4 w-4" /> Kembali ke halaman masuk
            </RouterLink>
        </div>
    </div>
</template>
