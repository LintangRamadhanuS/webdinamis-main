<script setup>
import { ref, onMounted } from 'vue';
import { RouterView, useRouter } from 'vue-router';
import ToastContainer from './components/ui/ToastContainer.vue';
import PageLoader from './components/ui/PageLoader.vue';

const router = useRouter();
const ready = ref(false);

onMounted(() => {
    // Tunggu resolusi navigasi pertama (termasuk pengecekan sesi login) sebelum
    // merender halaman, supaya tidak ada kedipan layar kosong saat load awal.
    router.isReady().finally(() => {
        ready.value = true;
    });
});
</script>

<template>
    <PageLoader v-if="!ready" />
    <RouterView v-else v-slot="{ Component }">
        <Transition name="page" mode="out-in">
            <component :is="Component" />
        </Transition>
    </RouterView>
    <ToastContainer />
</template>

<style>
.page-enter-active {
    transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.page-leave-active {
    transition: opacity 0.15s ease;
}
.page-enter-from {
    opacity: 0;
    transform: translateY(8px);
}
.page-leave-to {
    opacity: 0;
}
</style>
