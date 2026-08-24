<script setup>
import { onMounted, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import PageHeader from '../../components/ui/PageHeader.vue';
import EmptyState from '../../components/ui/EmptyState.vue';
import Pagination from '../../components/ui/Pagination.vue';
import SkeletonRow from '../../components/ui/SkeletonRow.vue';
import ConfirmModal from '../../components/ui/ConfirmModal.vue';
import Icon from '../../components/ui/Icon.vue';
import UserFormModal from '../../components/user/UserFormModal.vue';
import { useUser } from '../../composables/useUser';
import { useToast } from '../../composables/useToast';
import { useAuthStore } from '../../stores/auth';

const { items, loading, error, meta, filters, fetchUsers, goToPage, createUser, updateUser, deleteUser } = useUser();
const toast = useToast();
const auth = useAuthStore();

const showForm = ref(false);
const editingUser = ref(null);
const saving = ref(false);
const serverErrors = ref({});

const showConfirm = ref(false);
const deletingId = ref(null);
const deleting = ref(false);

const avatarPalette = ['from-indigo-400 to-violet-500', 'from-sky-400 to-cyan-500', 'from-amber-400 to-orange-500', 'from-emerald-400 to-teal-500', 'from-rose-400 to-pink-500'];

onMounted(fetchUsers);

function openCreate() {
    editingUser.value = null;
    serverErrors.value = {};
    showForm.value = true;
}

function openEdit(u) {
    editingUser.value = u;
    serverErrors.value = {};
    showForm.value = true;
}

async function handleSubmit(payload) {
    saving.value = true;
    serverErrors.value = {};
    try {
        if (editingUser.value) {
            await updateUser(editingUser.value.id, payload);
            toast.success('Pengguna berhasil diperbarui.');
        } else {
            await createUser(payload);
            toast.success('Pengguna berhasil ditambahkan.');
        }
        showForm.value = false;
        fetchUsers();
    } catch (err) {
        if (err.response?.status === 422) {
            serverErrors.value = err.response.data.errors;
        } else {
            toast.error('Terjadi kesalahan saat menyimpan data.');
        }
    } finally {
        saving.value = false;
    }
}

function askDelete(id) {
    deletingId.value = id;
    showConfirm.value = true;
}

async function confirmDelete() {
    deleting.value = true;
    try {
        await deleteUser(deletingId.value);
        toast.success('Pengguna berhasil dihapus.');
    } catch {
        toast.error('Gagal menghapus pengguna.');
    } finally {
        deleting.value = false;
        showConfirm.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <PageHeader title="Pengguna" subtitle="Kelola akun pengguna marketplace.">
            <template #actions>
                <button
                    @click="openCreate"
                    class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2.5 text-sm font-medium text-white shadow-md shadow-indigo-200 transition-all hover:shadow-lg active:scale-95 dark:shadow-indigo-950"
                >
                    <Icon name="plus" class="h-4 w-4" /> Tambah Pengguna
                </button>
            </template>
        </PageHeader>

        <div class="relative mb-4 max-w-sm animate-fade-in-up" style="animation-delay: 80ms">
            <Icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
            <input
                v-model="filters.q" type="text" placeholder="Cari nama atau email..."
                class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
            />
        </div>

        <p v-if="error" class="mb-4 flex items-center gap-2 rounded-lg bg-rose-50 px-4 py-2.5 text-sm text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
            <Icon name="alert" class="h-4 w-4 shrink-0" /> {{ error }}
        </p>

        <div class="animate-fade-in-up overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-white/10" style="animation-delay: 140ms">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800/60 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">NIM</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Fakultas</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <template v-if="loading">
                        <SkeletonRow v-for="n in 5" :key="n" :cols="6" />
                    </template>
                    <tr v-else-if="items.length === 0">
                        <td colspan="6">
                            <EmptyState icon="users" title="Tidak ada pengguna yang cocok" description="Coba ubah kata kunci pencarian." />
                        </td>
                    </tr>
                    <tr
                        v-else v-for="(u, i) in items" :key="u.id"
                        class="animate-fade-in-up transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/60"
                        :style="{ animationDelay: `${i * 40}ms` }"
                    >
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-xs font-bold text-white"
                                    :class="avatarPalette[u.id % avatarPalette.length]"
                                >
                                    {{ u.name.charAt(0).toUpperCase() }}
                                </div>
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ u.name }}</span>
                                <span v-if="u.id === auth.user?.id" class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-indigo-500 dark:bg-indigo-500/15 dark:text-indigo-400">Anda</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ u.nim }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ u.email }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">{{ u.fakultas ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                :class="u.role === 'admin' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
                            >
                                {{ u.role }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <button @click="openEdit(u)" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:text-slate-500 dark:hover:bg-indigo-500/15 dark:hover:text-indigo-400">
                                    <Icon name="pencil" class="h-4 w-4" />
                                </button>
                                <button
                                    @click="askDelete(u.id)" title="Hapus" :disabled="u.id === auth.user?.id"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-slate-400 dark:text-slate-500 dark:hover:bg-rose-500/15 dark:hover:text-rose-400 dark:disabled:hover:text-slate-500"
                                >
                                    <Icon name="trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            <Pagination :current-page="meta.current_page" :last-page="meta.last_page" @change="goToPage" />
        </div>

        <UserFormModal
            :show="showForm" :user="editingUser" :saving="saving" :server-errors="serverErrors"
            @submit="handleSubmit" @close="showForm = false"
        />
        <ConfirmModal
            :show="showConfirm" title="Hapus Pengguna" message="Akun pengguna yang dihapus tidak dapat dikembalikan. Lanjutkan?"
            :loading="deleting" @confirm="confirmDelete" @cancel="showConfirm = false"
        />
    </AppLayout>
</template>
