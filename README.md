# Bekas — Marketplace Barang Bekas Kampus

Panel admin untuk mengelola marketplace jual-beli barang bekas di lingkungan kampus.
Backend **Laravel 12** (REST API + Sanctum) dan frontend **Vue 3 SPA** (Vue Router + Pinia + Tailwind CSS v4).

## Fitur

- **Role admin & pengguna** — admin punya akses penuh (panel manajemen di bawah); pengguna
  biasa (mahasiswa) jelajah & beli barang sendiri, lihat riwayat transaksi & ulasan miliknya
  lewat halaman terpisah (Beranda, Jelajah Barang, Transaksi Saya, Ulasan Saya). Lihat bagian
  **Role & Otorisasi** di bawah.
- **Dashboard** (admin) — statistik live (6 kartu, polling otomatis), grafik transaksi 30 hari & sebaran kategori, aktivitas terbaru.
- **Jelajah Barang** (pengguna) — grid katalog barang tersedia, cari & filter kategori, detail barang
  dengan ulasan pembeli, tombol beli langsung.
- **Barang** — CRUD lengkap (admin), unggah foto (otomatis dikompres & di-resize, lihat
  bagian **Optimasi Foto**), filter kategori/status/kondisi, pencarian. Katalog bisa dilihat
  (read-only) oleh siapa saja yang login.
- **Kategori** — CRUD (admin), tidak bisa dihapus jika masih punya barang terkait.
- **Transaksi** — pengguna beli barang sendiri (status otomatis "pending"); admin meninjau &
  ubah status langsung dari tabel (status barang ikut disinkronkan otomatis — lihat di bawah).
- **Ulasan** — pengguna beri ulasan setelah transaksinya "selesai"; admin moderasi (lihat semua, hapus).
- **Pengguna** — CRUD akun (nama, NIM, email, fakultas, role) — khusus admin.
- **Profil Saya** (admin & pengguna) — ubah nama/email/fakultas sendiri, ganti password
  (perlu verifikasi password lama).
- **Lupa password** — kirim tautan reset lewat email dari halaman login (lihat catatan
  `MAIL_MAILER` di bawah untuk testing lokal).
- **Mode gelap** — toggle di sidebar (bawah, atas tombol Keluar), berlaku di seluruh halaman.
  Default mengikuti preferensi sistem saat pertama kali buka, lalu tersimpan di browser
  (localStorage) begitu dipilih manual. Lihat bagian **Mode Gelap** di bawah.

Semua halaman dilindungi login (Laravel Sanctum, session-based SPA auth).

## Menjalankan secara lokal

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# Hubungkan folder penyimpanan foto barang supaya bisa diakses lewat browser
# (otomatis ikut jalan tiap `composer install`, tapi aman dijalankan manual juga):
php artisan storage:link

# Pilihan A — SQLite (paling simpel, tidak perlu setup database server):
touch database/database.sqlite

# Pilihan B — MySQL: buat databasenya dulu (mis. lewat phpMyAdmin), lalu di .env
# ubah DB_CONNECTION=mysql dan isi DB_DATABASE/DB_USERNAME/DB_PASSWORD (lihat komentar di .env.example).

php artisan migrate --seed

# Jalankan backend & frontend bersamaan
composer run dev
```

`composer run dev` menjalankan server Laravel + Vite dev server + queue listener sekaligus
(lihat script `dev` di `composer.json`). Kalau lebih suka manual, bisa juga dua terminal terpisah:

```bash
php artisan serve   # terminal 1
npm run dev          # terminal 2
```

Buka `http://localhost:8000` — **pakai alamat ini persis** (bukan `127.0.0.1:8000` atau domain lain)
kecuali kamu sudah menyesuaikan `SANCTUM_STATEFUL_DOMAINS` di `.env` (lihat Troubleshooting di bawah).

### Login demo

Setelah `migrate --seed`, gunakan salah satu akun berikut (atau klik "Gunakan kredensial demo"
di halaman login untuk akun admin):

```
Admin     : admin@bekas.test       / password
Pengguna  : mahasiswa@bekas.test   / password
```

Login dengan akun admin untuk lihat panel manajemen penuh; login dengan akun pengguna untuk
coba jelajah barang, beli, dan beri ulasan setelah transaksinya ditandai "selesai" oleh admin.

Seeder juga membuat 8 pengguna acak tambahan (password sama: `password`, role `pengguna`),
6 kategori, 18 barang, 17 transaksi (tersebar 30 hari terakhir), dan 12 ulasan — supaya
dashboard langsung terlihat hidup saat pertama kali dijalankan, bukan kosong.

## Troubleshooting

**"Session store not set on request." saat login** — Sanctum SPA butuh session, dan session
hanya aktif untuk request yang originnya cocok dengan `SANCTUM_STATEFUL_DOMAINS` di `.env`.
Kalau kamu akses lewat alamat lain dari `localhost:8000` (mis. `127.0.0.1:8000`, IP jaringan,
atau domain custom XAMPP/Laragon seperti `webdinamis.test`), tambahkan alamat tersebut ke
`SANCTUM_STATEFUL_DOMAINS` di `.env`, contoh:

```
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000,webdinamis.test
```

Lalu jalankan `php artisan config:clear` dan coba lagi.

**Error terkait database saat `migrate`** — kalau pakai MySQL, pastikan databasenya sudah
dibuat lebih dulu (Laravel tidak membuatkan otomatis seperti file SQLite) dan kredensial di
`.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) sudah benar.

**Tautan "lupa password" tidak masuk ke email** — default `.env.example` memakai
`MAIL_MAILER=log`, artinya email tidak benar-benar terkirim; isinya (termasuk tautan reset)
ditulis ke `storage/logs/laravel.log`. Buka file itu, cari baris `Reset Password Notification`,
tautannya ada di situ. Untuk email sungguhan, isi `MAIL_MAILER=smtp` beserta `MAIL_HOST`/
`MAIL_PORT`/`MAIL_USERNAME`/`MAIL_PASSWORD` di `.env` (mis. pakai Mailtrap untuk testing).

**Tautan "lupa password" mengarah ke `http://localhost/...` (tanpa `:8000`) dan 404** —
link dibangun dari origin browser yang mengakses halaman "Lupa password" (dikirim otomatis
ke backend), jadi seharusnya sudah otomatis benar. Kalau masih salah, pastikan alamat yang
kamu pakai membuka halaman "Lupa password" itu sendiri ada di `SANCTUM_STATEFUL_DOMAINS`
(lihat poin pertama di atas) — kalau tidak terdaftar di situ, backend akan mengabaikannya
dan jatuh balik ke `APP_URL` di `.env`, jadi pastikan itu juga `http://localhost:8000`.

**Terminal menunjukkan `Terminate batch job (Y/N)?` berkali-kali & "exited with code 1"
saat Ctrl+C di Git Bash** — ini bukan bug di project, tapi perilaku Windows + Git Bash saat
menghentikan beberapa proses Node.js sekaligus (`concurrently` menjalankan 4 proses:
server, queue, logs, vite). Ketik `y` lalu Enter tiap kali prompt itu muncul (biasanya
1-3 kali) sampai kembali ke prompt biasa — servernya tetap berhenti dengan benar, cuma
konfirmasinya berulang. Kalau setelah itu `composer run dev` berikutnya gagal jalan karena
port 8000 "already in use", berarti ada proses `php.exe`/`node.exe` yang belum benar-benar
mati — cek & akhiri lewat Task Manager. Kalau ini sering mengganggu, jalankan `php artisan
serve` dan `npm run dev` di dua tab terpisah (lihat bagian **Menjalankan secara lokal**)
supaya Ctrl+C di tiap tab cuma perlu menghentikan satu proses.

**Foto barang "berhasil" diunggah tapi gambarnya tidak muncul (ikon rusak/placeholder)** —
foto-nya memang tersimpan di server, tapi belum bisa diakses lewat browser karena symlink
`public/storage` belum ada. Jalankan:

```bash
php artisan storage:link
```

Tidak perlu unggah ulang foto yang sudah ada — begitu symlink-nya dibuat, foto lama langsung
bisa diakses juga. (Mulai project ini, langkah ini otomatis ikut jalan tiap `composer install`
lewat script `post-install-cmd`, tapi kalau kamu sudah `composer install` sebelum pembaruan
ini, jalankan perintah di atas sekali secara manual.)

## Role & Otorisasi

Kolom `role` (`admin` atau `pengguna`, lihat `App\Enums\UserRole`) menentukan akses:

- **Admin** — akses penuh: semua halaman manajemen (Dashboard, Barang, Kategori, Transaksi,
  Ulasan, Pengguna).
- **Pengguna** — 4 halaman: Beranda (statistik pribadi), Jelajah Barang, Transaksi Saya,
  Ulasan Saya — semuanya di-scope otomatis ke akun sendiri.

Penegakan aturan terjadi di **backend** (bukan cuma disembunyikan di UI):

- Middleware `admin` (`app/Http/Middleware/EnsureUserIsAdmin.php`, alias didaftarkan di
  `bootstrap/app.php`) menolak (403) request non-admin ke rute yang di-wrap dengannya.
- Di `routes/api.php`: Pengguna sepenuhnya admin-only; Barang & Kategori bisa dibaca siapa
  saja yang login tapi hanya admin yang bisa mengubahnya; Transaksi & Ulasan bisa dibaca siapa
  saja (otomatis di-scope ke milik sendiri untuk non-admin) tapi hanya admin yang bisa
  membuat/mengubah/menghapus lewat endpoint CRUD biasa.
- Admin tidak bisa mengubah role akun sendiri — mencegah admin tidak sengaja mendemosi diri
  sendiri sampai terkunci dari panel.

### Alur beli & ulasan (pengguna)

Pengguna TIDAK memakai endpoint `POST /api/transaksi` biasa (itu tetap admin-only, untuk
mencatat transaksi manual). Sebagai gantinya ada dua endpoint khusus, terbuka untuk siapa
saja yang login, dengan aturan bisnisnya dijaga di dalam controller (bukan cuma middleware):

- `POST /api/barang/{barang}/beli` (`TransaksiController::beli`) — menolak (422) kalau barang
  sedang tidak "tersedia"; kalau berhasil, `user_id` dikunci ke `$request->user()->id` (tidak
  bisa membeli atas nama orang lain) dan status transaksi mulai dari "pending".
- `POST /api/barang/{barang}/ulasan` (`UlasanController::beriUlasan`) — menolak (403) kalau
  pengguna belum punya transaksi "selesai" untuk barang itu, dan menolak (422) kalau sudah
  pernah memberi ulasan untuk barang yang sama.
- `POST /api/transaksi/{transaksi}/batalkan` (`TransaksiController::batalkan`) — pengguna
  membatalkan transaksi miliknya sendiri selama masih "pending"/"diproses" (menolak 403 kalau
  bukan miliknya, 422 kalau sudah "selesai"/"dibatalkan"). Tombol "Batalkan" muncul di
  Transaksi Saya untuk transaksi yang masih bisa dibatalkan.
- Ulasan untuk `barang_id` tertentu selalu publik (siapa saja yang login bisa lihat, dipakai
  di halaman detail barang) — beda dengan daftar "Ulasan Saya"/moderasi admin yang di-scope
  ke pemilik.

**Status barang & transaksi disinkronkan otomatis** (`TransaksiController::syncBarangStatus`,
jalan tiap kali transaksi dibuat/diubah — baik lewat `beli()` maupun endpoint admin biasa):
status transaksi `pending`/`diproses` → barang `ditahan` (supaya tidak dibeli dobel), `selesai`
→ barang `terjual`, `dibatalkan` → barang `tersedia` lagi.

### Password & profil

- `PUT /api/profile` (`ProfileController::update`) — ubah nama/email/fakultas akun sendiri.
  Sengaja tidak menerima `nim` (identitas tetap) maupun `role` (cuma admin yang mengatur,
  lewat `PUT /api/user/{id}`, dan admin tidak bisa mengubah role dirinya sendiri).
- `PUT /api/profile/password` (`ProfileController::updatePassword`) — ganti password sendiri,
  wajib mengisi `current_password` yang divalidasi dengan rule bawaan Laravel `current_password`.
- `POST /api/forgot-password` / `POST /api/reset-password` (`AuthController`) — alur lupa
  password standar Laravel (`Password` broker + tabel `password_reset_tokens`, sudah ada dari
  migration bawaan), lewat notifikasi kustom `App\Notifications\ResetPasswordNotification`
  supaya tautan di email mengarah ke rute Vue `/reset-password`, bukan rute Blade bawaan
  Laravel yang tidak dipakai di SPA ini. URL-nya dibangun dari `frontend_url` yang dikirim
  `ForgotPassword.vue` (`window.location.origin`) — tapi hanya dipercaya kalau host-nya ada
  di `SANCTUM_STATEFUL_DOMAINS` (lihat `AuthController::resolveFrontendUrl()`), supaya
  endpoint ini tidak bisa disalahgunakan mengarahkan link ke domain lain. Kalau tidak
  terdaftar atau tidak dikirim, jatuh balik ke `APP_URL` di `.env`.

## Optimasi Foto

Foto barang otomatis dihapus dari server saat: barang dihapus, foto diganti lewat edit
(foto lama dihapus sebelum foto baru disimpan — kalau edit tidak mengganti foto, foto lama
dibiarkan), dan setiap `migrate:fresh --seed`/`db:seed` (folder `storage/app/public/barang`
dibersihkan di awal `BarangSeeder`, supaya foto uji coba dari sesi sebelumnya tidak menumpuk
jadi sampah tak-bertuan).

Foto barang otomatis di-resize & dikompres saat diunggah (`App\Services\ImageOptimizer`,
dipakai di `BarangController::store()`/`update()`):

- Sisi terpanjang dibatasi 1200px (foto lebih kecil dari itu tidak diperbesar).
- Selalu disimpan sebagai `.jpg` kualitas 80% terlepas dari format aslinya — JPEG jauh lebih
  hemat ukuran dibanding PNG untuk foto (bukan ikon/logo), dan transparansi PNG diisi latar
  putih dulu sebelum dikonversi (foto barang tidak butuh transparansi).
- Pakai ekstensi **GD bawaan PHP** (bukan package Composer tambahan) — hampir selalu aktif
  di instalasi PHP manapun termasuk XAMPP. Kalau entah kenapa tidak aktif di server kamu,
  otomatis jatuh balik menyimpan file asli tanpa optimasi (tidak fatal error).
- Validasi (`StoreBarangRequest`/`UpdateBarangRequest`): hanya menerima JPG/PNG/WEBP, maks
  8MB untuk file yang diunggah — cukup longgar untuk foto HP asli, karena ukuran akhir yang
  tersimpan sudah jauh lebih kecil setelah lewat optimasi di atas. `BarangFormModal.vue`
  mengecek tipe & ukuran ini juga di sisi klien supaya file yang jelas tidak valid langsung
  ditolak tanpa perlu round-trip ke server.
- Mau mengubah batas ukuran/kualitas? Tinggal ubah `MAX_DIMENSION`/`JPEG_QUALITY` di
  `ImageOptimizer` (dan angka yang cocok di `BarangFormModal.vue` supaya pesannya tetap sinkron).

## Mode Gelap

Diaktifkan lewat class `.dark` di `<html>` (bukan cuma ikut OS) — Tailwind v4 dikonfigurasi
lewat `@custom-variant dark (&:where(.dark, .dark *));` di `resources/css/app.css` (bukan
`tailwind.config.js`, karena project ini pakai setup CSS-first bawaan Tailwind v4).

- **Toggle**: `resources/js/composables/useTheme.js` — state `isDark` disimpan module-level
  (semua komponen berbagi satu instance), diterapkan otomatis ke `<html>` lewat `watchEffect`,
  tersimpan di `localStorage` (key `bekas-theme`). Default awal ikut
  `prefers-color-scheme` sistem kalau belum pernah dipilih manual.
- **Anti-kedip**: `resources/views/app.blade.php` punya script inline kecil yang membaca
  `localStorage`/preferensi sistem dan menambahkan class `.dark` **sebelum** apapun dirender
  (bukan menunggu Vue mount) — tanpa ini akan ada kedipan mode terang sesaat di reload kalau
  pengguna sudah memilih mode gelap.
- **Sidebar sengaja tetap gelap permanen** di kedua mode (bukan ikut toggle) — pilihan desain
  yang umum dipakai admin panel modern, bukan oversight.
- Kalau menambah halaman/komponen baru: setiap warna `bg-white`, `text-slate-*`,
  `border-slate-*`, `ring-slate-900/5`, `divide-slate-*` perlu pasangan `dark:` (lihat
  komponen mana pun sebagai contoh polanya — polanya konsisten di semua 30+ file yang ada).
  Warna gradien (tombol, badge ikon) sengaja TIDAK diberi varian gelap — sudah cukup kontras
  di kedua mode.

## Struktur singkat

```
app/Http/Controllers/Api/   Controller REST API (barang, kategori, transaksi, ulasan, user, dashboard)
resources/js/pages/         Satu folder per entitas + katalog/ (jelajah-beli untuk pengguna)
resources/js/components/    Komponen shared (ui/) dan spesifik-entitas (barang/, kategori/, katalog/, dst)
resources/js/composables/   Satu composable per entitas — pola fetch/create/update/delete + pagination
resources/js/layouts/       AppLayout.vue — sidebar responsif (menu berbeda untuk admin vs pengguna)
resources/css/app.css       Sistem desain: warna, animasi custom (Tailwind v4 @theme), efek glass/shimmer
```

## Catatan pengembangan

- Endpoint API ada di `routes/api.php`. Barang/Kategori/Transaksi/Ulasan mendaftarkan
  `apiResource(...)` dua kali dengan subset `only([...])` berbeda supaya read dan write bisa
  punya middleware berbeda (lihat bagian **Role & Otorisasi**); Pengguna tetap satu
  `apiResource(...)` penuh di dalam grup `admin`.
- Halaman **Ulasan** admin sengaja hanya "lihat + hapus" (moderasi) — admin tidak mengetik
  ulasan atas nama pengguna. Endpoint `POST /api/ulasan` standar tetap admin-only; pengguna
  membuat ulasan lewat endpoint terpisah `POST /api/barang/{barang}/ulasan` yang aturan
  bisnisnya beda (lihat **Role & Otorisasi**).
- Beberapa endpoint (`transaksi`, `ulasan`, `user`) mengembalikan paginator Laravel mentah
  (field pagination rata di root JSON), sedangkan `barang` dibungkus API Resource
  (`{data, links, meta}`). Composable masing-masing sudah disesuaikan dengan bentuk responsnya.
