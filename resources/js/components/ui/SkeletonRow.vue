<script setup>
// Baris skeleton generik dipakai di semua tabel (Barang, Kategori, Transaksi, Ulasan, Pengguna).
defineProps({
    cols: { type: Number, default: 5 },
});
const widths = ['w-32', 'w-20', 'w-24', 'w-16', 'w-16', 'w-28', 'w-12'];
</script>

<template>
    <tr>
        <td v-for="i in cols" :key="i" class="px-4 py-3.5">
            <div class="skeleton h-4 rounded" :class="widths[(i - 1) % widths.length]"></div>
        </td>
    </tr>
</template>
