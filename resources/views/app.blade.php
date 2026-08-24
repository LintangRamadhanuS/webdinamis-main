<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4338ca">
    <meta name="color-scheme" content="light dark">
    <meta name="description" content="Bekas — panel admin untuk mengelola marketplace barang bekas kampus: pengguna, barang, kategori, transaksi, dan ulasan.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Bekas — Marketplace Barang Bekas</title>

    <script>
        // Sinkron, jalan sebelum apapun dirender -- supaya tidak ada kedipan mode terang
        // sesaat sebelum Vue mount kalau pengguna sudah memilih mode gelap sebelumnya.
        (function () {
            var stored = localStorage.getItem('bekas-theme');
            var dark = stored === 'dark' || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) document.documentElement.classList.add('dark');
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div id="app">
        {{-- Splash statis: tampil sebelum Vue mount, lalu otomatis tertimpa saat app.mount('#app') jalan. --}}
        <div class="dark:hidden" style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#eef2ff,#faf5ff);">
            <div style="width:44px;height:44px;border-radius:9999px;border:3px solid #e0e7ff;border-top-color:#4f46e5;animation:spl-spin 0.8s linear infinite;"></div>
        </div>
        <div class="hidden dark:flex" style="min-height:100vh;align-items:center;justify-content:center;background:linear-gradient(135deg,#0f172a,#1e1b3a);">
            <div style="width:44px;height:44px;border-radius:9999px;border:3px solid #312e81;border-top-color:#818cf8;animation:spl-spin 0.8s linear infinite;"></div>
        </div>
    </div>
    <style>@keyframes spl-spin{to{transform:rotate(360deg)}}</style>
</body>
</html>
