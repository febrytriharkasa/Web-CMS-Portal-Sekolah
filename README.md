# SMALISKA Web

Portal sekolah SMA XYZ. Dibangun dengan Laravel, Inertia React, Tailwind CSS, dan MySQL atau SQLite.

## Fitur utama

- **CMS konten:** CRUD Berita, Laman statis, Kategori, Guru dan Tendik, Galeri (kategori `galeri`). Editor blok (teks, callout, blockquote, tabel) dan lampiran metadata per kategori.
- **Pengaturan dinamis via `site_settings`:** identitas situs, logo, menu navbar (`nav_items`), kolom footer (`footer_groups`), topbar, hero carousel, kontak, sosial media, layanan pintas, kartu PPDB sidebar, dan statistik sekolah.
- **AI Chatbot:** asisten virtual dengan konteks data sekolah, provider LLM opsional, dan jawaban cadangan berbasis kata kunci. Detail di [API Chatbot](#api-chatbot).
- **Pengelolaan pengguna:** tiga peran (`staff`, `admin`, `superadmin`), persetujuan akun baru, dan reset kata sandi oleh superadmin. Detail di [Akun dan peran](#akun-dan-peran).
- **SEO server-side:** judul, description, canonical, Open Graph, Twitter card, JSON-LD, plus `sitemap.xml` dan `robots.txt` dinamis.
- **Tema terang dan gelap:** toggle tersimpan di `localStorage`, ikut preferensi sistem.
- **Statistik publik:** jumlah berita, prestasi, agenda, galeri, guru, dan tendik dari data terbit.

## Kebutuhan

- PHP 8.3 atau lebih baru
- Composer 2
- Node.js 20 atau lebih baru
- npm
- MySQL 8 jika memakai MySQL

## Instalasi

```bash
git clone <repository-url>
cd sekolah-web
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Di macOS atau Linux, ganti `copy` dengan:

```bash
cp .env.example .env
```

Shortcut instalasi lengkap (install dependency, salin `.env`, generate key, migrate, lalu build aset):

```bash
composer run setup
```

## Konfigurasi lingkungan

Atur nilai berikut pada `.env`:

```env
APP_NAME=SMALISKA
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smaliska
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public
```

Jangan commit file `.env`. Isi `APP_KEY`, kredensial database, dan konfigurasi produksi hanya di server atau secret manager.

### Opsi SQLite

Untuk pengembangan tanpa MySQL:

```env
DB_CONNECTION=sqlite
```

Pastikan file `database/database.sqlite` tersedia, lalu jalankan migrasi.

### Opsi MySQL

Buat database terlebih dahulu:

```sql
CREATE DATABASE smaliska CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Lalu isi `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada `.env`.

## Database dan data awal

Jalankan migrasi dan seeder utama:

```bash
php artisan migrate --seed
```

Untuk menghapus seluruh tabel, membuat ulang skema, lalu mengisi ulang data awal:

```bash
php artisan migrate:fresh --seed
```

Perintah `migrate:fresh` menghapus seluruh data database aktif.

Seeder utama membuat akun pengelola, kategori, pengaturan situs, halaman, konten contoh, staf, dan galeri.

### Akun dan peran

Peran disimpan pada kolom `users.role` (string), bukan paket izin eksternal. Status akun memakai kolom `users.is_active`.

| Email | Password | Role |
| --- | --- | --- |
| `admin@smaxyz.sch.id` | `password` | superadmin |
| `humas@smaxyz.sch.id` | `password` | admin |
| `it@smaxyz.sch.id` | `password` | admin |
| `kurikulum@smaxyz.sch.id` | `password` | staff |
| `kesiswaan@smaxyz.sch.id` | `password` | staff |

Ganti password dummy sebelum deployment.

Akses halaman admin dibatasi middleware `active`, `admin`, dan `superadmin`. Alias terdaftar di `bootstrap/app.php`.

| Area | Middleware | Siapa yang boleh masuk |
| --- | --- | --- |
| `/admin/dashboard` | `active` | staff, admin, superadmin |
| `/admin/dashboard/toggle-chat`, `/admin/posts`, `/admin/pages`, `/admin/categories`, `/admin/staff`, `/admin/home-content`, `/admin/galleries`, `/admin/settings/*` | `admin` | admin, superadmin |
| `/admin/users` (ubah, hapus, reset kata sandi) | `superadmin` | superadmin |

Catatan perilaku akun:

- Pendaftaran publik (`/register`) terbuka. Pendaftar mendapat role `staff` dengan `is_active=false`, tidak langsung masuk, dan diarahkan ke halaman masuk dengan pesan menunggu persetujuan.
- Persetujuan akun dilakukan superadmin di `/admin/users`. Saat disetujui, status aktif di-set dan email verifikasi ikut ditandai terverifikasi.
- Lupa kata sandi tidak mengirim tautan reset. Pengguna mengirim permintaan di `/forgot-password`, sistem mencatat `password_reset_requested_at`, lalu superadmin mereset dari panel. Kata sandi acak 12 karakter dikirim lewat email.
- Pengaman: superadmin terakhir tidak bisa diturunkan perannya atau dinonaktifkan, dan akun yang sedang dipakai tidak bisa menghapus dirinya sendiri.

### Dummy post dengan tabel data

Jalankan seeder berikut untuk membuat tiga post dengan tabel pada metadata:

```bash
php artisan db:seed --class=DummyPostWithTableSeeder
```

Data dibuat atau diperbarui berdasarkan slug untuk kategori:

- Prestasi: hasil Olimpiade Sains
- Pengumuman: hasil seleksi delegasi kompetisi
- Ekstrakurikuler: perolehan medali Tapak Suci

Setiap tabel disimpan pada `metadata.achievement_table` dengan struktur berikut:

```json
{
  "achievement_table": [
    {
      "name": "Nama peserta atau kelas",
      "rank": "Juara atau peringkat",
      "event": "Lomba atau keterangan"
    }
  ]
}
```

## Penyimpanan gambar

Post dan galeri memakai disk `public`. Buat symbolic link sekali setelah instalasi:

```bash
php artisan storage:link
```

Gambar yang diunggah di-resize, dibersihkan dari metadata, dan dikonversi ke WebP sebelum disimpan.

## Menjalankan aplikasi

Untuk menjalankan Laravel dan Vite bersamaan:

```bash
composer run dev
```

Atau jalankan terpisah:

```bash
php artisan serve
npm run dev
```

Build aset produksi:

```bash
npm run build
```

## Halaman publik dan route

| Route | Keterangan |
| --- | --- |
| `/` | Beranda: hero, layanan pintas, berita, seksi per kategori, galeri, guru dan tendik |
| `/berita` | Arsip berita dengan pagination |
| `/{slug}` | Laman statis atau arsip kategori |
| `/{category}/{slug}` | Detail post (navigasi sebelumnya dan berikutnya, post terkait) |
| `/cari` | Pencarian kata kunci pada judul post |
| `/statistik` | Statistik sekolah |
| `/sitemap.xml`, `/robots.txt` | SEO dinamis |

URL GET yang tidak dikenal tetap memakai chrome situs (navbar, logo, footer) dengan status 404.

## API Chatbot

### Arsitektur

Alur jawaban ada di `App\Services\Chatbot\ChatResponder`, dipanggil oleh `ChatBotController`.

```text
POST /api/chat
  └── ChatBotController@send
        ├── cek SiteSetting chat_enabled (0 = nonaktif, balas 403)
        ├── simpan pesan user ke chat_messages
        ├── ChatResponder@respond
        │     ├── driver off            → KeywordFallback
        │     ├── kuota harian habis    → KeywordFallback
        │     ├── SchoolContext@for     → system prompt (identitas + konten relevan)
        │     ├── provider berurutan    → OpenAiCompatibleClient
        │     └── semua gagal / tanpa key → KeywordFallback
        └── simpan jawaban ke chat_messages, balas { session_token, reply }
```

Komponen:

| Berkas | Peran |
| --- | --- |
| `app/Http/Controllers/ChatBotController.php` | Validasi request, simpan riwayat, kirim balasan |
| `app/Services/Chatbot/ChatResponder.php` | Orkestrator: provider LLM, fallback, kuota harian, riwayat percakapan |
| `app/Services/Chatbot/OpenAiCompatibleClient.php` | HTTP client endpoint OpenAI-compatible (Gemini, Groq, provider lain) |
| `app/Services/Chatbot/SchoolContext.php` | System prompt: profil sekolah dari `site_settings`, halaman terbit, dan 3 post serta 2 laman paling relevan dari kata kunci pertanyaan |
| `app/Services/Chatbot/KeywordFallback.php` | Jawaban tanpa LLM (PPDB, alamat, kontak, program akademik) dengan fakta dari `site_settings` |
| `config/chatbot.php` | Driver, daftar provider, timeout, token, temperatur, batas riwayat, kuota harian |
| `resources/js/Components/ChatWidgetPanel.jsx` | Widget publik: kirim pesan, pulihkan riwayat dari `sessionStorage` |

Catatan perilaku:

- Provider dilewati kalau `api_key` kosong. Tidak ada API key berarti chatbot tetap jalan dengan `KeywordFallback`.
- Riwayat percakapan yang dikirim ke model dibatasi `CHATBOT_HISTORY_LIMIT` pesan terakhir dan ikut menyertakan konteks sekolah.
- Kuota harian LLM dibatasi `CHATBOT_DAILY_LIMIT` request. Setelah habis, jawaban memakai fallback sampai hari berganti.
- Jawaban fallback deterministik dan seluruh faktanya dari `site_settings`, jadi tidak berbeda dengan jawaban AI.

### Konfigurasi

```env
CHATBOT_DRIVER=auto
GEMINI_API_KEY=
CHATBOT_GEMINI_MODEL=gemini-3.5-flash-lite
GROQ_API_KEY=
CHATBOT_GROQ_MODEL=openai/gpt-oss-120b
CHATBOT_TIMEOUT=15
CHATBOT_MAX_TOKENS=400
CHATBOT_HISTORY_LIMIT=8
CHATBOT_DAILY_LIMIT=300
```

| Variabel | Fungsi |
| --- | --- |
| `CHATBOT_DRIVER` | `auto` (coba provider, lalu fallback) atau `off` (langsung fallback, tanpa panggilan LLM) |
| `GEMINI_API_KEY`, `GROQ_API_KEY` | API key provider. Kosong berarti provider itu dilewati |
| `CHATBOT_GEMINI_MODEL`, `CHATBOT_GROQ_MODEL` | Nama model per provider |
| `CHATBOT_TIMEOUT` | Batas waktu request ke provider, dalam detik |
| `CHATBOT_MAX_TOKENS` | Maksimum token jawaban |
| `CHATBOT_HISTORY_LIMIT` | Jumlah pesan sebelumnya yang dikirim sebagai konteks |
| `CHATBOT_DAILY_LIMIT` | Kuota request LLM per hari sebelum jatuh ke fallback |

Urutan provider diatur pada `config/chatbot.php`. Menambah provider berarti menambah entri `base_url`, `model`, dan `api_key` di sana.

### Endpoint

**`POST /api/chat`** mengirim pesan dan menerima balasan.

Body JSON:

| Field | Wajib | Aturan |
| --- | --- | --- |
| `message` | ya | string, maksimum 1000 karakter |
| `session_token` | tidak | UUID. Kalau kosong atau bukan UUID, server membuat sesi baru dan mengembalikan tokennya |

Balasan sukses `200`:

```json
{
  "session_token": "9f1c2b3a-4d5e-6f70-8a9b-0c1d2e3f4a5b",
  "reply": "Pendaftaran Peserta Didik Baru (PPDB) SMA Al Islam Krian saat ini telah dibuka..."
}
```

Simpan `session_token` di sisi klien dan kirim ulang pada pesan berikutnya supaya percakapan tetap satu sesi dan kuota per sesi terhitung benar.

**`GET /api/chat/history?session_token=<uuid>`** mengembalikan sampai 50 pesan terakhir dari sesi tersebut, urut lama ke baru.

```json
{
  "messages": [
    { "role": "user", "content": "Kapan PPDB dibuka?" },
    { "role": "assistant", "content": "Pendaftaran Peserta Didik Baru (PPDB)..." }
  ]
}
```

Sesi yang tidak dikenal dibalas `{"messages": []}`, bukan 404, supaya klien tidak perlu menangani kasus khusus.

### Contoh pemanggilan

Kedua route berada di middleware grup `web`, jadi butuh cookie sesi dan header CSRF. Widget mengambil token dari `<meta name="csrf-token">` dan mengirimnya sebagai `X-CSRF-TOKEN`.

```bash
# Ambil cookie sesi dan token CSRF dari halaman publik
curl -s -c cookies.txt http://localhost:8000/ > page.html
TOKEN=$(grep -o 'name="csrf-token" content="[^"]*"' page.html | sed 's/.*content="//;s/"//')

# Kirim pesan
curl -s -b cookies.txt -X POST http://localhost:8000/api/chat \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $TOKEN" \
  -d '{"message":"Kapan PPDB dibuka?"}'

# Lanjutkan sesi yang sama
curl -s -b cookies.txt -X POST http://localhost:8000/api/chat \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: $TOKEN" \
  -d '{"message":"Apa syaratnya?","session_token":"<uuid-dari-balasan-sebelumnya>"}'

# Baca riwayat
curl -s -b cookies.txt -H "Accept: application/json" \
  "http://localhost:8000/api/chat/history?session_token=<uuid>"
```

### Kode balasan

| Kode | Arti |
| --- | --- |
| `200` | Balasan berhasil dibuat (dari provider LLM atau dari fallback) |
| `403` | Fitur chat dinonaktifkan admin (`chat_enabled = 0`). Body: `{"error": "Fitur chat sedang dinonaktifkan."}` |
| `419` | Cookie sesi atau token CSRF tidak ada atau kedaluwarsa |
| `422` | Validasi gagal: `message` kosong, lebih dari 1000 karakter, atau `session_token` bukan UUID |
| `429` | Rate limit terlampaui |

### Rate limit

Batas dipasang di `AppServiceProvider::boot()` lewat `RateLimiter::for('chat')` dan diterapkan pada route dengan `throttle:chat`.

| Batas | Kunci |
| --- | --- |
| 8 request per menit | per `session_token`, atau per IP kalau token kosong |
| 60 request per hari | per `session_token`, atau per IP kalau token kosong |
| 20 request per menit | per IP |

Batas per IP sengaja lebih longgar karena jaringan sekolah biasanya keluar lewat satu IP publik. Endpoint riwayat memakai `throttle:60,1`.

### Menonaktifkan chatbot

Dua cara:

- Semua LLM: set `CHATBOT_DRIVER=off`. Chatbot tetap membalas dengan `KeywordFallback` dan tidak ada request keluar.
- Seluruh fitur: matikan lewat tombol di dashboard admin (`POST /admin/dashboard/toggle-chat`). Endpoint balas `403`, widget menampilkan pesan gangguan.

### Test

```bash
php artisan test --filter=ChatBotTest
```

Mencakup: chat nonaktif, validasi input, jawaban tersimpan dan dikembalikan, provider gagal jatuh ke fallback, tanpa API key, driver `off`, rate limit, batas riwayat, kuota harian, dan penyimpanan jawaban fallback.

### Catatan keamanan

- API key provider hanya dibaca backend dari `.env`. Jangan menaruhnya di kode frontend.
- System prompt membatasi model agar hanya menjawab berdasarkan konteks sekolah dan menolak permintaan mengubah peran atau keluar dari topik.
- Widget mengingatkan pengunjung untuk tidak mengirim data pribadi. Riwayat disimpan di tabel `chat_messages` tanpa pembersihan otomatis, jadi hapus berkala kalau perlu.

## SEO

- Komponen `Seo` (`resources/js/Components/Seo.jsx`) memasang judul halaman, meta description, canonical, Open Graph, Twitter card, dan JSON-LD.
- `GET /sitemap.xml` dibuat `SitemapController` dari laman dan post terbit.
- `GET /robots.txt` melarang `/admin`, `/cari`, dan `/login`, serta menunjuk ke sitemap.

## Pengujian dan format kode

```bash
php artisan test
vendor/bin/pint --format agent
npm run build
```

Status per 2026-10-21: 231 tes / 1510 assertion lulus (29 file Feature dan 1 Unit). Test frontend dan E2E belum ada, jadi interaksi dropdown, modal, dark mode, navbar mobile, dan upload masih diverifikasi manual.

## Struktur penting

```text
app/Http/Controllers/Admin/PostController.php     Validasi dan simpan post
app/Http/Controllers/ChatBotController.php        Endpoint chat dan riwayat
app/Services/Chatbot/ChatResponder.php            Orkestrator jawaban chatbot
app/Services/Chatbot/SchoolContext.php            Konteks sekolah untuk prompt
app/Http/Controllers/SitemapController.php        Sitemap dinamis
resources/js/Components/ChatWidgetPanel.jsx       Widget chat publik
resources/js/Pages/Admin/Posts/Form.jsx           Form post dan editor tabel
resources/js/Pages/Public/PostDetail.jsx          Tampilan detail post publik
database/seeders/DatabaseSeeder.php               Data awal aplikasi
database/seeders/DummyPostWithTableSeeder.php     Data dummy tabel post
config/chatbot.php                                Konfigurasi chatbot
```