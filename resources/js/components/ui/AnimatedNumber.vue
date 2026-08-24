<script setup>
// Angka yang "menghitung naik" secara halus tiap kali value berubah — dipakai di StatCard, dsb.
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    value: { type: [Number, String], default: 0 },
    duration: { type: Number, default: 900 },
});

const display = ref(0);
let raf = null;

function animateTo(target) {
    const start = display.value;
    const diff = target - start;
    const startTime = performance.now();

    if (raf) cancelAnimationFrame(raf);
    if (!diff) {
        display.value = target;
        return;
    }

    function tick(now) {
        const progress = Math.min((now - startTime) / props.duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
        display.value = Math.round(start + diff * eased);
        if (progress < 1) raf = requestAnimationFrame(tick);
    }
    raf = requestAnimationFrame(tick);
}

watch(() => props.value, (v) => animateTo(Number(v) || 0));
onMounted(() => animateTo(Number(props.value) || 0));
onUnmounted(() => raf && cancelAnimationFrame(raf));
</script>

<template>
    <span>{{ display.toLocaleString('id-ID') }}</span>
</template>
