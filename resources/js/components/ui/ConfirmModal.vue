<script setup>
import Icon from './Icon.vue';

defineProps({
    show: Boolean,
    title: { type: String, default: 'Konfirmasi' },
    message: { type: String, default: 'Apakah Anda yakin?' },
    confirmText: { type: String, default: 'Hapus' },
    confirmingText: { type: String, default: 'Menghapus...' },
    loading: Boolean,
});
const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
                @click.self="emit('cancel')"
            >
                <Transition
                    enter-active-class="transition duration-250 ease-out"
                    enter-from-class="opacity-0 scale-90"
                    leave-active-class="transition duration-150 ease-in"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="show" class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400">
                            <Icon name="alert" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-4 font-semibold text-slate-800 dark:text-slate-100">{{ title }}</h3>
                        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">{{ message }}</p>
                        <div class="mt-6 flex justify-end gap-2">
                            <button @click="emit('cancel')" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">
                                Batal
                            </button>
                            <button
                                @click="emit('confirm')"
                                :disabled="loading"
                                class="flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-medium text-white shadow-md shadow-rose-200 transition-colors hover:bg-rose-700 disabled:cursor-not-allowed disabled:bg-rose-300 disabled:shadow-none dark:shadow-rose-950 dark:disabled:bg-rose-800"
                            >
                                <span v-if="loading" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                                {{ loading ? confirmingText : confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
