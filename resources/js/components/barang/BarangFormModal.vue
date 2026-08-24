<script setup>
import { reactive, computed, watch, ref } from 'vue';
import Icon from '../ui/Icon.vue';

const props = defineProps({
    show: Boolean,
    barang: { type: Object, default: null }, // null = mode create
    kategoris: { type: Array, default: () => [] },
    saving: Boolean,
    serverErrors: { type: Object, default: () => ({}) },
});
const emit = defineEmits(['submit', 'close']);

const form = reactive({
    nama: '', deskripsi: '', harga: '', kondisi: 'bekas - layak pakai',
    status: 'tersedia', kategori_id: '', foto: null,
});
const fotoPreview = ref(null);
const fotoError = ref('');
const fotoSizeLabel = ref('');

const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
const MAX_SIZE_BYTES = 8 * 1024 * 1024; // 8MB -- sama dengan batas di StoreBarangRequest/UpdateBarangRequest

function formatBytes(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

watch(() => props.barang, (b) => {
    form.nama = b?.nama ?? '';
    form.deskripsi = b?.deskripsi ?? '';
    form.harga = b?.harga ?? '';
    form.kondisi = b?.kondisi ?? 'bekas - layak pakai';
    form.status = b?.status ?? 'tersedia';
    form.kategori_id = b?.kategori_id ?? '';
    form.foto = null;
    fotoPreview.value = b?.foto_url ?? null;
}, { immediate: true });

// Validasi real-time di client — mengurangi round-trip ke server untuk error yang jelas
const errors = computed(() => {
    const e = {};
    if (form.nama && form.nama.length < 3) e.nama = 'Nama minimal 3 karakter.';
    if (form.harga !== '' && Number(form.harga) < 0) e.harga = 'Harga tidak boleh negatif.';
    if (form.deskripsi && form.deskripsi.length > 2000) e.deskripsi = 'Deskripsi maksimal 2000 karakter.';
    return e;
});

const isValid = computed(() =>
    form.nama && form.deskripsi && form.harga !== '' && form.kategori_id &&
    Object.keys(errors.value).length === 0 && !fotoError.value
);

function onFileChange(e) {
    const file = e.target.files[0];
    fotoError.value = '';
    fotoSizeLabel.value = '';

    if (!file) {
        form.foto = null;
        return;
    }

    if (!ALLOWED_TYPES.includes(file.type)) {
        fotoError.value = 'Format harus JPG, PNG, atau WEBP.';
        e.target.value = '';
        return;
    }
    if (file.size > MAX_SIZE_BYTES) {
        fotoError.value = `Ukuran ${formatBytes(file.size)} melebihi batas 8MB.`;
        e.target.value = '';
        return;
    }

    form.foto = file;
    fotoPreview.value = URL.createObjectURL(file);
    fotoSizeLabel.value = formatBytes(file.size);
}

function handleSubmit() {
    if (!isValid.value) return;
    const fd = new FormData();
    Object.entries(form).forEach(([key, val]) => {
        if (val !== null && val !== '') fd.append(key, val);
    });
    emit('submit', fd);
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
                    <form v-if="show" @submit.prevent="handleSubmit" class="max-h-[90vh] w-full max-w-lg space-y-4 overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white shadow-md shadow-indigo-200 dark:shadow-indigo-950">
                                <Icon name="box" class="h-5 w-5" />
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                                {{ barang ? 'Edit Barang' : 'Tambah Barang' }}
                            </h3>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Nama Barang</label>
                            <input v-model="form.nama" type="text"
                                class="mt-1.5 w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                                :class="(errors.nama || serverErrors.nama) ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'" />
                            <p v-if="errors.nama || serverErrors.nama?.[0]" class="mt-1 text-xs text-rose-500">
                                {{ errors.nama || serverErrors.nama[0] }}
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Deskripsi</label>
                            <textarea v-model="form.deskripsi" rows="3"
                                class="mt-1.5 w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                                :class="(errors.deskripsi || serverErrors.deskripsi) ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'"></textarea>
                            <p v-if="errors.deskripsi" class="mt-1 text-xs text-rose-500">{{ errors.deskripsi }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Harga (Rp)</label>
                                <input v-model="form.harga" type="number" min="0"
                                    class="mt-1.5 w-full rounded-lg border px-3 py-2 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30"
                                    :class="errors.harga ? 'border-rose-400 dark:border-rose-500' : 'border-slate-300 dark:border-slate-600'" />
                                <p v-if="errors.harga" class="mt-1 text-xs text-rose-500">{{ errors.harga }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Kategori</label>
                                <div class="relative mt-1.5">
                                    <select v-model="form.kategori_id" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-9 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                                        <option value="" disabled>Pilih kategori</option>
                                        <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
                                    </select>
                                    <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Kondisi</label>
                                <div class="relative mt-1.5">
                                    <select v-model="form.kondisi" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-9 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                                        <option value="baru">Baru</option>
                                        <option value="bekas - seperti baru">Bekas - seperti baru</option>
                                        <option value="bekas - layak pakai">Bekas - layak pakai</option>
                                        <option value="bekas - butuh perbaikan">Bekas - butuh perbaikan</option>
                                    </select>
                                    <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Status</label>
                                <div class="relative mt-1.5">
                                    <select v-model="form.status" class="w-full appearance-none rounded-lg border border-slate-300 bg-white py-2 pl-3 pr-9 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 dark:focus:ring-indigo-500/30">
                                        <option value="tersedia">Tersedia</option>
                                        <option value="terjual">Terjual</option>
                                        <option value="ditahan">Ditahan</option>
                                    </select>
                                    <Icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-slate-600 dark:text-slate-300">Foto</label>
                            <div class="mt-1.5 flex items-start gap-4">
                                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900">
                                    <img v-if="fotoPreview" :src="fotoPreview" class="h-full w-full object-cover" />
                                    <Icon v-else name="image" class="h-7 w-7 text-slate-300 dark:text-slate-600" />
                                </div>
                                <div class="flex-1">
                                    <label class="relative flex w-fit cursor-pointer items-center gap-2 rounded-lg border border-dashed border-slate-300 px-4 py-2 text-sm text-slate-600 transition-colors hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-600 dark:text-slate-300 dark:hover:border-indigo-400 dark:hover:text-indigo-400">
                                        <Icon name="camera" class="h-4 w-4" />
                                        {{ fotoPreview ? 'Ganti foto' : 'Unggah foto' }}
                                        <input type="file" accept="image/jpeg,image/png,image/webp" @change="onFileChange" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" />
                                    </label>
                                    <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">JPG, PNG, atau WEBP, maks 8MB — otomatis dikompres.</p>
                                    <p v-if="fotoSizeLabel && !fotoError" class="mt-0.5 text-xs text-emerald-600 dark:text-emerald-400">{{ fotoSizeLabel }} dipilih</p>
                                    <p v-if="fotoError" class="mt-0.5 text-xs text-rose-500">{{ fotoError }}</p>
                                </div>
                            </div>
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
