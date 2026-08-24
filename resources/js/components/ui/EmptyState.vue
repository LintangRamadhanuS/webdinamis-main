<script setup>
import Icon from './Icon.vue';

defineProps({
    icon: { type: String, default: 'inbox' },
    title: { type: String, default: 'Belum ada data' },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-14 text-center animate-fade-in">
        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-fuchsia-50 text-indigo-400 animate-float dark:from-indigo-500/10 dark:to-fuchsia-500/10 dark:text-indigo-400">
            <Icon :name="icon" class="h-8 w-8" />
        </div>
        <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ title }}</h3>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-slate-400 dark:text-slate-500">{{ description }}</p>
        <div v-if="$slots.default" class="mt-4">
            <slot />
        </div>
    </div>
</template>
