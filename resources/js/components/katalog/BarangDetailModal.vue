<script setup>
import { ref, watch } from 'vue';
import Icon from '../ui/Icon.vue';
import EmptyState from '../ui/EmptyState.vue';
import api from '../../lib/axios';

const props = defineProps({
    show: Boolean,
    barang: { type: Object, default: null },
    buying: Boolean,
});
const emit = defineEmits(['close', 'beli']);

const ulasan = ref([]);
const loadingUlasan = ref(false);

// Ulasan barang tertentu bersifat publik (lihat UlasanController@index) -- siapa pun yang
// login boleh melihat semua ulasan produk ini, bukan cuma miliknya sendiri.
watch(() => props.barang, async (b) => {
    if (!b) return;
    loadingUlasan.value = true;
    ulasan.value = [];
    try {
        const { data } = await api.get('/api/ulasan', { params: { barang_id: b.id, per_page: 20 } });
        ulasan.value = data.data;
    } catch {
        ulasan.value = [];
    } finally {
        loadingUlasan.value = false;
    }
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0"
            leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0"
        >
            <div v-if="show && barang" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" @click.self="emit('close')">
                <Transition
                    enter-active-class="transition duration-250 ease-out" enter-from-class="opacity-0 scale-95"
                    leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="show && barang" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl dark:bg-slate-800">
                        <div class="relative">
                            <div class="flex h-56 items-center justify-center bg-slate-100 sm:h-64 dark:bg-slate-900">
                                <img v-if="barang.foto_url" :src="barang.foto_url" class="h-full w-full object-cover" :alt="barang.nama" />
                                <Icon v-else name="image" class="h-16 w-16 text-slate-300 dark:text-slate-600" />
                            </div>
                            <button
                                @click="emit('close')"
                                class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-slate-600 shadow-md backdrop-blur-sm transition-colors hover:bg-white dark:bg-slate-800/90 dark:text-slate-300 dark:hover:bg-slate-800"
                            >
                                <Icon name="x" class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400">
                                        {{ barang.kategori?.nama_kategori ?? 'Tanpa kategori' }}
                                    </span>
                                    <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">{{ barang.nama }}</h2>
                                    <p class="mt-1 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ barang.harga_format }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs capitalize text-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ barang.kondisi }}</span>
                            </div>

                            <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ barang.deskripsi }}</p>

                            <button
                                @click="emit('beli', barang.id)" :disabled="buying"
                                class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition-all duration-200 hover:shadow-xl active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 dark:shadow-indigo-950"
                            >
                                <span v-if="buying" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                                <Icon v-else name="swap" class="h-4 w-4" />
                                {{ buying ? 'Memproses...' : 'Beli Barang Ini' }}
                            </button>

                            <div class="mt-6 border-t border-slate-100 pt-5 dark:border-slate-700">
                                <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-200">Ulasan Pembeli</h3>

                                <div v-if="loadingUlasan" class="space-y-3">
                                    <div v-for="i in 2" :key="i">
                                        <div class="skeleton h-3.5 w-1/4 rounded"></div>
                                        <div class="skeleton mt-2 h-8 w-full rounded"></div>
                                    </div>
                                </div>

                                <EmptyState
                                    v-else-if="!ulasan.length" icon="star" title="Belum ada ulasan"
                                    description="Jadilah yang pertama memberi ulasan setelah membeli."
                                />

                                <div v-else class="space-y-4">
                                    <div v-for="u in ulasan" :key="u.id">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">{{ u.user?.name ?? 'Pengguna' }}</p>
                                            <div class="flex shrink-0 items-center gap-0.5">
                                                <Icon
                                                    v-for="n in 5" :key="n" name="star" class="h-3.5 w-3.5 fill-current"
                                                    :class="n <= u.rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700'"
                                                />
                                            </div>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ u.komentar }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
