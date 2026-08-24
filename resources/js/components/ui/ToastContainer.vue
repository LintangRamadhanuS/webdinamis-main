<script setup>
import { useToast } from '../../composables/useToast';
import Icon from './Icon.vue';

const { toasts } = useToast();

const styles = {
    success: { icon: 'check-circle', accent: 'bg-emerald-500', iconBg: 'bg-emerald-50 dark:bg-emerald-500/15', iconText: 'text-emerald-600 dark:text-emerald-400' },
    error: { icon: 'alert', accent: 'bg-rose-500', iconBg: 'bg-rose-50 dark:bg-rose-500/15', iconText: 'text-rose-600 dark:text-rose-400' },
    info: { icon: 'bell', accent: 'bg-slate-500', iconBg: 'bg-slate-100 dark:bg-slate-700', iconText: 'text-slate-600 dark:text-slate-300' },
};
</script>

<template>
    <div class="fixed bottom-4 right-4 z-50 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-2.5">
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-x-8 scale-95"
            leave-active-class="absolute transition duration-200 ease-in"
            leave-to-class="opacity-0 scale-90"
            move-class="transition-transform duration-300"
        >
            <div
                v-for="t in toasts"
                :key="t.id"
                class="relative flex w-full items-start gap-3 overflow-hidden rounded-xl bg-white p-3.5 pl-4 shadow-lg ring-1 ring-slate-900/5 dark:bg-slate-800 dark:ring-white/10"
            >
                <span class="absolute inset-y-0 left-0 w-1" :class="(styles[t.type] ?? styles.info).accent"></span>
                <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                    :class="[(styles[t.type] ?? styles.info).iconBg, (styles[t.type] ?? styles.info).iconText]"
                >
                    <Icon :name="(styles[t.type] ?? styles.info).icon" class="h-4 w-4" />
                </div>
                <p class="mt-1 text-sm leading-snug text-slate-700 dark:text-slate-200">{{ t.message }}</p>
            </div>
        </TransitionGroup>
    </div>
</template>
