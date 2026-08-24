<script setup>
import { reactive, computed, watch } from 'vue';
import Icon from '../ui/Icon.vue';
import { useAuthStore } from '../../stores/auth';

const props = defineProps({
    show: Boolean,
    user: { type: Object, default: null }, // null = mode create
    saving: Boolean,
    serverErrors: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['submit', 'close']);

const auth = useAuthStore();
const isEditingSelf = computed(() => !!props.user && props.user.id === auth.user?.id);

const form = reactive({ name: '', nim: '', email: '', fakultas: '', role: 'pengguna', password: '' });

watch(() => props.user, (u) => {
    form.name = u?.name ?? '';
    form.nim = u?.nim ?? '';
    form.email = u?.email ?? '';
    form.fakultas = u?.fakultas ?? '';
    form.role = u?.role ?? 'pengguna';
    form.password = '';
}, { immediate: true });

// Cermin aturan Password::min(8)->mixedCase()->numbers() di backend (lihat UserController).
const passwordIssues = computed(() => {
    if (props.user || !form.password) return [];
    const pw = form.password;
    const issues = [];
    if (pw.length < 8) issues.push('minimal 8 karakter');
    if (!/[a-z]/.test(pw)) issues.push('huruf kecil');
    if (!/[A-Z]/.test(pw)) issues.push('huruf besar');
    if (!/[0-9]/.test(pw)) issues.push('angka');
    return issues;
});

const errors = computed(() => {
    const e = {};
    if (form.email && !/^\S+@\S+\.\S+$/.test(form.email)) e.email = 'Format email tidak valid.';
    return e;
});

const isValid = computed(() => {
    if (!form.name || !form.nim || !form.email) return false;
    if (Object.keys(errors.value).length) return false;
    if (!props.user && (!form.password || passwordIssues.value.length)) return false;
    return true;
});

function handleSubmit() {
    if (!isValid.value) return;
    const payload = { name: form.name, nim: form.nim, email: form.email, fakultas: form.fakultas };
    if (!isEditingSelf.value) payload.role = form.role;
    if (!props.user) payload.password = form.password;
    emit('submit', payload);
}
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
            leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" @click.self="emit('close')">
                <Transition
                    enter-active-class="transition duration-250 ease-out" enter-from-class="opacity-0 scale-95"
                    leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0 scale-95"
                >
                    <form v-if="show" @submit.prevent="handleSubmit" class="max-h-[90vh] w-full max-w-md space-y-4 overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-md shadow-indigo-200 dark:shadow-indigo-950">
                                <Icon name="users" class="h-5 w-5" />
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ user ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h3>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Nama Lengkap</label>
                            <input v-model="form.name" type="text"
                                class="mt-1.5 w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                                :class="serverErrors.name ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'" />
                            <p v-if="serverErrors.name?.[0]" class="mt-1 text-xs text-rose-500">{{ serverErrors.name[0] }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-300">NIM</label>
                                <div class="relative mt-1.5">
                                    <Icon name="hash" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                    <input v-model="form.nim" type="text"
                                        class="w-full rounded-lg border py-2 pl-9 pr-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                                        :class="serverErrors.nim ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'" />
                                </div>
                                <p v-if="serverErrors.nim?.[0]" class="mt-1 text-xs text-rose-500">{{ serverErrors.nim[0] }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Fakultas</label>
                                <div class="relative mt-1.5">
                                    <Icon name="briefcase" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                    <input v-model="form.fakultas" type="text" placeholder="Opsional"
                                        class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30" />
                                </div>
                            </div>
                        </div>

                        <div v-if="!isEditingSelf">
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Role</label>
                            <div class="relative mt-1.5">
                                <select v-model="form.role" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                                    <option value="pengguna">Pengguna</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                            </div>
                            <p v-if="serverErrors.role?.[0]" class="mt-1 text-xs text-rose-500">{{ serverErrors.role[0] }}</p>
                        </div>
                        <div v-else class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2.5 text-xs text-slate-400 dark:bg-slate-900 dark:text-slate-500">
                            <Icon name="lock" class="h-3.5 w-3.5 shrink-0" /> Anda tidak dapat mengubah role akun sendiri.
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Email</label>
                            <div class="relative mt-1.5">
                                <Icon name="mail" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                <input v-model="form.email" type="email"
                                    class="w-full rounded-lg border py-2 pl-9 pr-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                                    :class="(errors.email || serverErrors.email) ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'" />
                            </div>
                            <p v-if="errors.email || serverErrors.email?.[0]" class="mt-1 text-xs text-rose-500">
                                {{ errors.email || serverErrors.email[0] }}
                            </p>
                        </div>

                        <div v-if="!user">
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Password</label>
                            <div class="relative mt-1.5">
                                <Icon name="lock" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                <input v-model="form.password" type="password"
                                    class="w-full rounded-lg border py-2 pl-9 pr-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                                    :class="(passwordIssues.length || serverErrors.password) ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'" />
                            </div>
                            <p class="mt-1 text-xs" :class="passwordIssues.length ? 'text-rose-500' : 'text-slate-400 dark:text-slate-500'">
                                Wajib memiliki huruf besar, huruf kecil, angka, dan minimal 8 karakter.
                            </p>
                        </div>
                        <div v-else class="rounded-lg bg-slate-50 px-3 py-2.5 text-xs text-slate-400 dark:bg-slate-900 dark:text-slate-500">
                            Password tidak dapat diubah dari form ini.
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="emit('close')" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">
                                Batal
                            </button>
                            <button type="submit" :disabled="!isValid || saving"
                                class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-medium text-white shadow-md shadow-indigo-200 transition-all hover:shadow-lg active:scale-[0.98] disabled:cursor-not-allowed disabled:from-slate-300 disabled:to-slate-300 disabled:shadow-none dark:shadow-indigo-950 dark:disabled:from-slate-700 dark:disabled:to-slate-700">
                                <span v-if="saving" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                                {{ saving ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
