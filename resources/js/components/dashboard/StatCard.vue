<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import Icon from '../ui/Icon.vue';
import AnimatedNumber from '../ui/AnimatedNumber.vue';

const props = defineProps({
    label: String,
    value: [Number, String],
    icon: { type: String, default: 'box' },
    color: { type: String, default: 'indigo' },
    loading: Boolean,
    to: { type: [String, Object], default: null },
    delay: { type: Number, default: 0 },
});

const palettes = {
    indigo: { grad: 'from-indigo-500 to-violet-500', shadow: 'shadow-indigo-200/70' },
    emerald: { grad: 'from-emerald-500 to-teal-500', shadow: 'shadow-emerald-200/70' },
    amber: { grad: 'from-amber-500 to-orange-500', shadow: 'shadow-amber-200/70' },
    rose: { grad: 'from-rose-500 to-pink-500', shadow: 'shadow-rose-200/70' },
    sky: { grad: 'from-sky-500 to-cyan-500', shadow: 'shadow-sky-200/70' },
    violet: { grad: 'from-violet-500 to-fuchsia-500', shadow: 'shadow-violet-200/70' },
};
const palette = computed(() => palettes[props.color] ?? palettes.indigo);
</script>

<template>
    <component
        :is="to ? RouterLink : 'div'"
        :to="to ?? undefined"
        class="group relative block animate-fade-in-up overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 lift-on-hover dark:bg-slate-900 dark:ring-white/10"
        :style="{ animationDelay: `${delay}ms` }"
    >
        <div class="flex items-start justify-between">
            <div class="min-w-0">
                <p class="break-words text-sm font-medium leading-snug text-slate-500 dark:text-slate-400">{{ label }}</p>
                <div class="mt-2 h-8">
                    <div v-if="loading" class="skeleton h-7 w-16 rounded-lg"></div>
                    <p v-else class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        <AnimatedNumber :value="value ?? 0" />
                    </p>
                </div>
            </div>
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-lg transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6"
                :class="[palette.grad, palette.shadow]"
            >
                <Icon :name="icon" class="h-5 w-5" />
            </div>
        </div>

        <div
            class="absolute inset-x-0 bottom-0 h-1 origin-left scale-x-0 bg-gradient-to-r transition-transform duration-500 ease-out group-hover:scale-x-100"
            :class="palette.grad"
        ></div>
    </component>
</template>
