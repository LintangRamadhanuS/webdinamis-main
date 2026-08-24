<script setup>
import { reactive, computed, ref, onMounted } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import Icon from '../../components/ui/Icon.vue';
import { useAuthStore } from '../../stores/auth';
import { useToast } from '../../composables/useToast';
import api from '../../lib/axios';

const auth = useAuthStore();
const toast = useToast();

const profileForm = reactive({ name: '', email: '', fakultas: '' });
const savingProfile = ref(false);
const profileErrors = ref({});

const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' });
const savingPassword = ref(false);
const passwordErrors = ref({});

onMounted(() => {
    profileForm.name = auth.user?.name ?? '';
    profileForm.email = auth.user?.email ?? '';
    profileForm.fakultas = auth.user?.fakultas ?? '';
});

const initials = computed(() => {
    const name = auth.user?.name?.trim();
    if (!name) return '?';
    const parts = name.split(/\s+/);
    return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase();
});

const passwordIssues = computed(() => {
    if (!passwordForm.password) return [];
    const pw = passwordForm.password;
    const issues = [];
    if (pw.length < 8) issues.push('minimal 8 karakter');
    if (!/[a-z]/.test(pw)) issues.push('huruf kecil');
    if (!/[A-Z]/.test(pw)) issues.push('huruf besar');
    if (!/[0-9]/.test(pw)) issues.push('angka');
    return issues;
});
const passwordValid = computed(() =>
    passwordForm.current_password && passwordForm.password &&
    passwordForm.password === passwordForm.password_confirmation && !passwordIssues.value.length
);

async function saveProfile() {
    savingProfile.value = true;
    profileErrors.value = {};
    try {
        const { data } = await api.put('/api/profile', profileForm);
        auth.setUser(data);
        toast.success('Profil berhasil diperbarui.');
    } catch (err) {
        if (err.response?.status === 422) profileErrors.value = err.response.data.errors;
        else toast.error('Gagal memperbarui profil.');
    } finally {
        savingProfile.value = false;
    }
}

async function savePassword() {
    if (!passwordValid.value) return;
    savingPassword.value = true;
    passwordErrors.value = {};
    try {
        await api.put('/api/profile/password', passwordForm);
        toast.success('Password berhasil diubah.');
        passwordForm.current_password = '';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';
    } catch (err) {
        if (err.response?.status === 422) passwordErrors.value = err.response.data.errors;
        else toast.error('Gagal mengubah password.');
    } finally {
        savingPassword.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <PageHeader title="Profil Saya" subtitle="Kelola informasi akun dan password kamu." />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Kartu identitas -->
            <div class="animate-fade-in-up rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-slate-900/5 lg:col-span-1 dark:bg-slate-900 dark:ring-white/10">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-fuchsia-500 text-2xl font-bold text-white shadow-lg shadow-indigo-200 dark:shadow-indigo-950">
                    {{ initials }}
                </div>
                <h2 class="mt-4 font-semibold text-slate-800 dark:text-slate-100">{{ auth.user?.name }}</h2>
                <p class="text-sm text-slate-400 dark:text-slate-500">{{ auth.user?.email }}</p>
                <span
                    class="mt-3 inline-block rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                    :class="auth.isAdmin ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
                >
                    {{ auth.user?.role }}
                </span>
                <div v-if="auth.user?.nim" class="mt-4 flex items-center justify-center gap-1.5 text-xs text-slate-400 dark:text-slate-500">
                    <Icon name="hash" class="h-3.5 w-3.5" /> {{ auth.user.nim }}
                </div>
            </div>

            <div class="space-y-6 lg:col-span-2">
                <!-- Form info profil -->
                <form
                    @submit.prevent="saveProfile"
                    class="animate-fade-in-up space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10"
                    style="animation-delay: 80ms"
                >
                    <h3 class="font-semibold text-slate-700 dark:text-slate-200">Informasi Akun</h3>

                    <div>
                        <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Nama Lengkap</label>
                        <input
                            v-model="profileForm.name" type="text"
                            class="mt-1.5 w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                            :class="profileErrors.name ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'"
                        />
                        <p v-if="profileErrors.name?.[0]" class="mt-1 text-xs text-rose-500">{{ profileErrors.name[0] }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Email</label>
                            <input
                                v-model="profileForm.email" type="email"
                                class="mt-1.5 w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                                :class="profileErrors.email ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'"
                            />
                            <p v-if="profileErrors.email?.[0]" class="mt-1 text-xs text-rose-500">{{ profileErrors.email[0] }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Fakultas</label>
                            <input
                                v-model="profileForm.fakultas" type="text" placeholder="Opsional"
                                class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit" :disabled="savingProfile"
                            class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-medium text-white shadow-md shadow-indigo-200 transition-all hover:shadow-lg active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-indigo-950"
                        >
                            <span v-if="savingProfile" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                            {{ savingProfile ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </button>
                    </div>
                </form>

                <!-- Form ganti password -->
                <form
                    @submit.prevent="savePassword"
                    class="animate-fade-in-up space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10"
                    style="animation-delay: 140ms"
                >
                    <h3 class="font-semibold text-slate-700 dark:text-slate-200">Ganti Password</h3>

                    <div>
                        <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Password Saat Ini</label>
                        <input
                            v-model="passwordForm.current_password" type="password"
                            class="mt-1.5 w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                            :class="passwordErrors.current_password ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'"
                        />
                        <p v-if="passwordErrors.current_password?.[0]" class="mt-1 text-xs text-rose-500">{{ passwordErrors.current_password[0] }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Password Baru</label>
                            <input
                                v-model="passwordForm.password" type="password"
                                class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                            />
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Konfirmasi</label>
                            <input
                                v-model="passwordForm.password_confirmation" type="password"
                                class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                            />
                        </div>
                    </div>
                    <p class="text-xs" :class="passwordIssues.length ? 'text-rose-500' : 'text-slate-400 dark:text-slate-500'">
                        Wajib huruf besar, huruf kecil, angka, minimal 8 karakter.
                    </p>

                    <div class="flex justify-end">
                        <button
                            type="submit" :disabled="!passwordValid || savingPassword"
                            class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2 text-sm font-medium text-white shadow-md shadow-amber-200 transition-all hover:shadow-lg active:scale-[0.98] disabled:cursor-not-allowed disabled:from-slate-300 disabled:to-slate-300 disabled:shadow-none dark:shadow-amber-950 dark:disabled:from-slate-700 dark:disabled:to-slate-700"
                        >
                            <span v-if="savingPassword" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                            {{ savingPassword ? 'Menyimpan...' : 'Ubah Password' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
