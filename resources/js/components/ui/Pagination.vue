<script setup>
import { computed } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
    currentPage: { type: Number, required: true },
    lastPage: { type: Number, required: true },
});
const emit = defineEmits(['change']);

// Truncated page list: selalu tampilkan halaman pertama/terakhir + sekitar current, sisanya "…".
const pages = computed(() => {
    const total = props.lastPage;
    const current = props.currentPage;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);

    const set = new Set([1, 2, total - 1, total, current - 1, current, current + 1]);
    const filtered = [...set].filter((p) => p >= 1 && p <= total).sort((a, b) => a - b);

    const withGaps = [];
    filtered.forEach((p, i) => {
        if (i > 0 && p - filtered[i - 1] > 1) withGaps.push('...');
        withGaps.push(p);
    });
    return withGaps;
});

function go(p) {
    if (p === '...' || p === props.currentPage || p < 1 || p > props.lastPage) return;
    emit('change', p);
}
</script>

<template>
    <div v-if="lastPage > 1" class="flex items-center justify-center gap-1.5">
        <button
            type="button"
            :disabled="currentPage === 1"
            @click="go(currentPage - 1)"
            class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 disabled:opacity-30 disabled:hover:bg-transparent dark:text-slate-400 dark:hover:bg-slate-800"
        >
            <Icon name="chevron-left" class="h-4 w-4" />
        </button>

        <template v-for="(p, i) in pages" :key="`${p}-${i}`">
            <span v-if="p === '...'" class="w-9 text-center text-sm text-slate-400 dark:text-slate-500">…</span>
            <button
                v-else
                type="button"
                @click="go(p)"
                class="relative h-9 w-9 rounded-lg text-sm font-medium transition-all duration-200"
                :class="p === currentPage ? 'scale-105 bg-indigo-600 text-white shadow-md shadow-indigo-200 dark:shadow-indigo-950' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'"
            >
                {{ p }}
            </button>
        </template>

        <button
            type="button"
            :disabled="currentPage === lastPage"
            @click="go(currentPage + 1)"
            class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 disabled:opacity-30 disabled:hover:bg-transparent dark:text-slate-400 dark:hover:bg-slate-800"
        >
            <Icon name="chevron-right" class="h-4 w-4" />
        </button>
    </div>
</template>
