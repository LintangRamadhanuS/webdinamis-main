<script setup>
import { reactive, computed, watch } from 'vue';
import Icon from '../ui/Icon.vue';

const props = defineProps({
    show: Boolean,
    kategori: { type: Object, default: null }, // null = mode create
    saving: Boolean,
    serverErrors: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['submit', 'close']);

const form = reactive({ nama_kategori: '' });

watch(() => props.kategori, (k) => {
    form.nama_kategori = k?.nama_kategori ?? '';
}, { immediate: true });

const errors = computed(() => {
    const e = {};
    if (form.nama_kategori && form.nama_kategori.length < 3) e.nama_kategori = 'Nama minimal 3 karakter.';
    return e;
});
const isValid = computed(() => form.nama_kategori && Object.keys(errors.value).length === 0);

function handleSubmit() {
    if (!isValid.value) return;
    emit('submit', { nama_kategori: form.nama_kategori });
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
                    <form v-if="show" @submit.prevent="handleSubmit" class="w-full max-w-sm space-y-4 rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 text-white shadow-md shadow-sky-200 dark:shadow-sky-950">
                                <Icon name="tag" class="h-5 w-5" />
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ kategori ? 'Edit Kategori' : 'Tambah Kategori' }}</h3>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Nama Kategori</label>
                            <input
                                v-model="form.nama_kategori" type="text" placeholder="mis. Elektronik"
                                class="mt-1.5 w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                                :class="(errors.nama_kategori || serverErrors.nama_kategori) ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'"
                            />
                            <p v-if="errors.nama_kategori || serverErrors.nama_kategori?.[0]" class="mt-1 text-xs text-rose-500">
                                {{ errors.nama_kategori || serverErrors.nama_kategori[0] }}
                            </p>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="emit('close')" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">
                                Batal
                            </button>
                            <button
                                type="submit" :disabled="!isValid || saving"
                                class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-medium text-white shadow-md shadow-indigo-200 transition-all hover:shadow-lg active:scale-[0.98] disabled:cursor-not-allowed disabled:from-slate-300 disabled:to-slate-300 disabled:shadow-none dark:shadow-indigo-950 dark:disabled:from-slate-700 dark:disabled:to-slate-700"
                            >
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
