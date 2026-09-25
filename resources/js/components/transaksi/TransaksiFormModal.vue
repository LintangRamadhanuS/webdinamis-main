<script setup>
import { reactive, computed, watch } from 'vue';
import Icon from '../ui/Icon.vue';

const props = defineProps({
    show: Boolean,
    transaksi: { type: Object, default: null }, // null = mode create
    users: { type: Array, default: () => [] },
    barangs: { type: Array, default: () => [] },
    saving: Boolean,
    serverErrors: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['submit', 'close']);

function toDatetimeLocal(date) {
    const pad = (n) => String(n).padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

const form = reactive({ user_id: '', barang_id: '', status: 'pending', tanggal: '' });

watch(() => props.transaksi, (t) => {
    form.user_id = t?.user?.id ?? '';
    form.barang_id = t?.barang?.id ?? '';
    form.status = t?.status ?? 'pending';
    // "t.tanggal" dari API sekarang ISO UTC (setelah cast 'datetime' di model Transaksi),
    // jadi konversi ke waktu lokal browser dulu sebelum diisikan ke input datetime-local.
    form.tanggal = t?.tanggal ? toDatetimeLocal(new Date(t.tanggal)) : toDatetimeLocal(new Date());
}, { immediate: true });

const isValid = computed(() => {
    if (!props.transaksi && (!form.user_id || !form.barang_id)) return false;
    return form.status && form.tanggal;
});

function handleSubmit() {
    if (!isValid.value) return;
    const payload = { status: form.status, tanggal: new Date(form.tanggal).toISOString() };
    if (!props.transaksi) {
        payload.user_id = form.user_id;
        payload.barang_id = form.barang_id;
    }
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
                    <form v-if="show" @submit.prevent="handleSubmit" class="w-full max-w-md space-y-4 rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shadow-amber-200 dark:shadow-amber-950">
                                <Icon name="swap" class="h-5 w-5" />
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">{{ transaksi ? 'Edit Transaksi' : 'Catat Transaksi' }}</h3>
                        </div>

                        <template v-if="!transaksi">
                            <div>
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Pembeli</label>
                                <div class="relative mt-1.5">
                                    <select v-model="form.user_id" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                                        <option value="" disabled>Pilih pembeli</option>
                                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                    </select>
                                    <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Barang</label>
                                <div class="relative mt-1.5">
                                    <select v-model="form.barang_id" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                                        <option value="" disabled>Pilih barang</option>
                                        <option v-for="b in barangs" :key="b.id" :value="b.id">{{ b.nama }}</option>
                                    </select>
                                    <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="rounded-lg bg-slate-50 px-3 py-2.5 text-sm dark:bg-slate-900">
                                <p class="text-slate-400 dark:text-slate-500">Pembeli &amp; Barang</p>
                                <p class="font-medium text-slate-700 dark:text-slate-200">{{ transaksi.user?.name ?? '-' }} &middot; {{ transaksi.barang?.nama ?? '-' }}</p>
                                <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">Tidak dapat diubah setelah transaksi dibuat.</p>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Status</label>
                                <div class="relative mt-1.5">
                                    <select v-model="form.status" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-9 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                                        <option value="pending">Pending</option>
                                        <option value="diproses">Diproses</option>
                                        <option value="selesai">Selesai</option>
                                        <option value="dibatalkan">Dibatalkan</option>
                                    </select>
                                    <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Tanggal</label>
                                <input v-model="form.tanggal" type="datetime-local"
                                    class="mt-1.5 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30 dark:[color-scheme:dark]" />
                            </div>
                        </div>

                        <p v-if="serverErrors.user_id || serverErrors.barang_id" class="text-xs text-rose-500">
                            {{ (serverErrors.user_id ?? serverErrors.barang_id)[0] }}
                        </p>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" @click="emit('close')" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700">
                                Batal
                            </button>
                            <button type="submit" :disabled="!isValid || saving"
                                class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2 text-sm font-medium text-white shadow-md shadow-amber-200 transition-all hover:shadow-lg active:scale-[0.98] disabled:cursor-not-allowed disabled:from-slate-300 disabled:to-slate-300 disabled:shadow-none dark:shadow-amber-950 dark:disabled:from-slate-700 dark:disabled:to-slate-700">
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
