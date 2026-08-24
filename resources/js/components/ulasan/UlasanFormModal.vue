<script setup>
import { reactive, computed, ref } from 'vue';
import Icon from '../ui/Icon.vue';

const props = defineProps({
    show: Boolean,
    barangNama: { type: String, default: '' },
    saving: Boolean,
});
const emit = defineEmits(['submit', 'close']);

const form = reactive({ rating: 0, komentar: '' });
const hoverRating = ref(0);

const isValid = computed(() => form.rating > 0 && form.komentar.trim().length > 0);

function handleSubmit() {
    if (!isValid.value) return;
    emit('submit', { rating: form.rating, komentar: form.komentar });
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
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-md shadow-amber-200 dark:shadow-amber-950">
                                <Icon name="star" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">Beri Ulasan</h3>
                                <p class="truncate text-xs text-slate-400 dark:text-slate-500">{{ barangNama }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Rating</label>
                            <div class="mt-2 flex items-center gap-1" @mouseleave="hoverRating = 0">
                                <button
                                    v-for="n in 5" :key="n" type="button"
                                    @click="form.rating = n" @mouseenter="hoverRating = n"
                                    class="p-0.5 transition-transform hover:scale-110"
                                >
                                    <Icon
                                        name="star" class="h-7 w-7 fill-current transition-colors"
                                        :class="n <= (hoverRating || form.rating) ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700'"
                                    />
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Komentar</label>
                            <textarea
                                v-model="form.komentar" rows="3" placeholder="Ceritakan pengalamanmu dengan barang ini..."
                                class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="emit('close')" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">
                                Batal
                            </button>
                            <button
                                type="submit" :disabled="!isValid || saving"
                                class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2 text-sm font-medium text-white shadow-md shadow-amber-200 transition-all hover:shadow-lg active:scale-[0.98] disabled:cursor-not-allowed disabled:from-slate-300 disabled:to-slate-300 disabled:shadow-none dark:shadow-amber-950 dark:disabled:from-slate-700 dark:disabled:to-slate-700"
                            >
                                <span v-if="saving" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                                {{ saving ? 'Mengirim...' : 'Kirim Ulasan' }}
                            </button>
                        </div>
                    </form>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
