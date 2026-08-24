import { ref, watchEffect } from 'vue';

const STORAGE_KEY = 'bekas-theme'; // 'light' | 'dark'

// Modul-level (bukan di dalam function) supaya semua komponen yang memanggil
// useTheme() berbagi state yang sama persis -- pola singleton composable sederhana.
const isDark = ref(resolveInitialTheme());

function resolveInitialTheme() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'dark') return true;
    if (stored === 'light') return false;
    // Belum pernah pilih manual -- ikuti preferensi sistem sebagai default awal.
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

// Terapkan ke <html> setiap kali isDark berubah, dan simpan pilihannya.
watchEffect(() => {
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light');
});

export function useTheme() {
    function toggle() {
        isDark.value = !isDark.value;
    }

    return { isDark, toggle };
}
