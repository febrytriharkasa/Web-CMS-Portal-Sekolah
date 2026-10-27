<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([UserSeeder::class]);

        $admin = User::where('email', 'admin@smaxyz.sch.id')->first();
        $humas = User::where('email', 'humas@smaxyz.sch.id')->first();

        // 1. Categories (semua konten lewat posts)
        $catBerita = Category::firstOrCreate(['slug' => 'berita'], ['name' => 'Berita', 'show_on_home' => true]);
        $catPengumuman = Category::firstOrCreate(['slug' => 'pengumuman'], ['name' => 'Pengumuman', 'show_on_home' => true]);
        $catAgenda = Category::firstOrCreate(['slug' => 'agenda'], [
            'name' => 'Agenda',
            'show_on_home' => true,
            'metadata_schema' => [
                ['key' => 'event_date', 'label' => 'Tanggal Agenda', 'type' => 'date'],
                ['key' => 'location', 'label' => 'Lokasi', 'type' => 'text'],
            ],
        ]);
        $catPrestasi = Category::firstOrCreate(['slug' => 'prestasi'], ['name' => 'Prestasi', 'show_on_home' => true]);
        $catFasilitas = Category::firstOrCreate(['slug' => 'fasilitas'], ['name' => 'Fasilitas', 'show_on_home' => true]);
        $catAlumni = Category::firstOrCreate(['slug' => 'cerita-alumni'], ['name' => 'Cerita Alumni', 'show_on_home' => true]);
        $catKegiatan = Category::firstOrCreate(['slug' => 'kegiatan'], ['name' => 'Kegiatan Sekolah']);
        $catEkstra = Category::firstOrCreate(['slug' => 'ekstrakurikuler'], ['name' => 'Ekstrakurikuler']);

        // 2. Site Settings
        $settings = [
            ['key' => 'site_name',       'value' => 'SMA XYZ',                                      'type' => 'text',  'group_name' => 'general'],
            ['key' => 'site_tagline',    'value' => 'Berakhlak Mulia, Berprestasi Tinggi, Berwawasan Global',  'type' => 'text',  'group_name' => 'general'],
            ['key' => 'hero_title',      'value' => 'Membentuk Generasi Rabbani & Berprestasi',                'type' => 'text',  'group_name' => 'hero'],
            ['key' => 'hero_subtitle',   'value' => 'Selamat Datang di Portal Resmi SMA XYZ. Pendidikan Islam modern berbasis teknologi & akhlakul karimah di Sidoarjo.', 'type' => 'text', 'group_name' => 'hero'],
            ['key' => 'hero_background', 'value' => '#065F46',                                                 'type' => 'color', 'group_name' => 'hero'],
            ['key' => 'hero_slide_1_tag',      'value' => 'Portal resmi SMA XYZ',                                  'type' => 'text',  'group_name' => 'hero'],
            ['key' => 'hero_slide_1_title',    'value' => 'Membentuk Generasi Rabbani & Berprestasi',                'type' => 'text',  'group_name' => 'hero'],
            ['key' => 'hero_slide_1_subtitle', 'value' => 'Selamat Datang di Portal Resmi SMA XYZ. Pendidikan Islam modern berbasis teknologi & akhlakul karimah di Sidoarjo.', 'type' => 'text', 'group_name' => 'hero'],
            ['key' => 'hero_slide_1_btn_text', 'value' => 'Sambutan Kepala Sekolah',                                'type' => 'text',  'group_name' => 'hero'],
            ['key' => 'hero_slide_1_btn_url',  'value' => '#sambutan',                                               'type' => 'text',  'group_name' => 'hero'],
            ['key' => 'hero_slide_1_image',    'value' => '',                                                       'type' => 'image', 'group_name' => 'hero'],

            ['key' => 'hero_slide_2_tag',      'value' => 'PPDB Online 2025/2026',                                  'type' => 'text',  'group_name' => 'hero'],
            ['key' => 'hero_slide_2_title',    'value' => 'Pendaftaran Peserta Didik Baru',                         'type' => 'text',  'group_name' => 'hero'],
            ['key' => 'hero_slide_2_subtitle', 'value' => 'Buka peluang masa depan terbaikmu bersama SMA XYZ. Bergabunglah dengan pendidikan Islam modern berwawasan global.', 'type' => 'text', 'group_name' => 'hero'],
            ['key' => 'hero_slide_2_btn_text', 'value' => 'Daftar Sekarang',                                         'type' => 'text',  'group_name' => 'hero'],
            ['key' => 'hero_slide_2_btn_url',  'value' => 'https://ppdb.smaxyz.sch.id',                            'type' => 'text',  'group_name' => 'hero'],
            ['key' => 'hero_slide_2_image',    'value' => '',                                                       'type' => 'image', 'group_name' => 'hero'],
            ['key' => 'contact_phone',   'value' => '(031) 8971234',                                           'type' => 'text',  'group_name' => 'contact'],
            ['key' => 'contact_email',   'value' => 'info@smaxyz.sch.id',                                    'type' => 'text',  'group_name' => 'contact'],
            ['key' => 'contact_address', 'value' => 'Jl. Kyai Mojo No. 1, Krian, Sidoarjo, Jawa Timur 61262', 'type' => 'text',  'group_name' => 'contact'],
            ['key' => 'ppdb_link',       'value' => 'https://ppdb.smaxyz.sch.id',                            'type' => 'text',  'group_name' => 'links'],
            ['key' => 'sidebar_cta_enabled',     'value' => '1',                  'type' => 'text', 'group_name' => 'sidebar_cta'],
            ['key' => 'sidebar_cta_title',       'value' => 'PPDB 2026 / 2027',   'type' => 'text', 'group_name' => 'sidebar_cta'],
            ['key' => 'sidebar_cta_heading',     'value' => 'Bergabung Bersama Generasi Unggul SMA XYZ', 'type' => 'text', 'group_name' => 'sidebar_cta'],
            ['key' => 'sidebar_cta_description', 'value' => 'Pendaftaran Gelombang 1 telah dibuka. Dapatkan jalur beasiswa tahfidz dan prestasi akademik.', 'type' => 'text', 'group_name' => 'sidebar_cta'],
            ['key' => 'sidebar_cta_button_text', 'value' => 'Daftar Sekarang',    'type' => 'text', 'group_name' => 'sidebar_cta'],
            ['key' => 'sidebar_cta_button_url',  'value' => 'https://ppdb.smaxyz.sch.id', 'type' => 'text', 'group_name' => 'sidebar_cta'],
            ['key' => 'quick_link_1_title', 'value' => 'Pendaftaran', 'type' => 'text', 'group_name' => 'links'],
            ['key' => 'quick_link_1_url',   'value' => 'https://ppdb.smaxyz.sch.id', 'type' => 'text', 'group_name' => 'links'],
            ['key' => 'quick_link_1_desc',  'value' => 'Portal Resmi Pendaftaran PPDB Online', 'type' => 'text', 'group_name' => 'links'],
            ['key' => 'quick_link_2_title', 'value' => 'SMA XYZ via mobile', 'type' => 'text', 'group_name' => 'links'],
            ['key' => 'quick_link_2_url',   'value' => 'https://mobile.smaxyz.or.id', 'type' => 'text', 'group_name' => 'links'],
            ['key' => 'quick_link_2_desc',  'value' => 'Aplikasi Layanan Mobile SMA XYZ', 'type' => 'text', 'group_name' => 'links'],
            ['key' => 'quick_link_3_title', 'value' => 'perpustakaan SMA XYZ', 'type' => 'text', 'group_name' => 'links'],
            ['key' => 'quick_link_3_url',   'value' => 'https://perpus.smaxyz.or.id', 'type' => 'text', 'group_name' => 'links'],
            ['key' => 'quick_link_3_desc',  'value' => 'Layanan Perpustakaan Digital', 'type' => 'text', 'group_name' => 'links'],
            ['key' => 'welcome_message', 'value' => "Assalamu'alaikum Wr. Wb. Segala puji syukur kami panjatkan ke hadirat Allah SWT atas rahmat dan hidayah-Nya sehingga SMA XYZ terus konsisten menjadi pelopor sekolah Islam berprestasi di Jawa Timur. Kami memadukan Kurikulum Merdeka dengan pendidikan karakter Al-Qur'an dan penguasaan teknologi digital modern untuk menyiapkan lulusan yang siap bersaing secara global.", 'type' => 'text', 'group_name' => 'general'],
            ['key' => 'principal_name',  'value' => 'Dr. H. Ahmad Dahlan, M.Pd.',                             'type' => 'text',  'group_name' => 'general'],
            ['key' => 'social_facebook',   'value' => 'https://facebook.com/SMAXYZ',        'type' => 'text',  'group_name' => 'social'],
            ['key' => 'social_instagram',  'value' => 'https://instagram.com/SMAXYZ',       'type' => 'text',  'group_name' => 'social'],
            ['key' => 'social_twitter',    'value' => 'https://twitter.com/SMAXYZ',          'type' => 'text',  'group_name' => 'social'],
            ['key' => 'social_youtube',    'value' => 'https://youtube.com/SMAXYZ',          'type' => 'text',  'group_name' => 'social'],
            ['key' => 'social_linkedin',   'value' => 'https://linkedin.com/company/SMAXYZ', 'type' => 'text',  'group_name' => 'social'],
            ['key' => 'social_whatsapp',   'value' => 'https://wa.me/628123456789',            'type' => 'text',  'group_name' => 'social'],
            ['key' => 'social_tiktok',     'value' => 'https://tiktok.com/@SMAXYZ',          'type' => 'text',  'group_name' => 'social'],
            ['key' => 'principal_photo', 'value' => '',                                                        'type' => 'image', 'group_name' => 'general'],
            ['key' => 'total_students',  'value' => '850+',                                                    'type' => 'text',  'group_name' => 'statistics'],
            ['key' => 'total_teachers',  'value' => '45',                                                      'type' => 'text',  'group_name' => 'statistics'],
            ['key' => 'total_staff',     'value' => '18',                                                      'type' => 'text',  'group_name' => 'statistics'],
            ['key' => 'year_founded',    'value' => '1978',                                                    'type' => 'text',  'group_name' => 'statistics'],
            ['key' => 'chat_enabled',          'value' => '1',                        'type' => 'boolean', 'group_name' => 'features'],
            ['key' => 'chat_assistant_name',   'value' => 'SMA XYZ Virtual Assistant', 'type' => 'text',   'group_name' => 'features'],
            ['key' => 'chat_greeting',         'value' => "Assalamu'alaikum! Saya {name}, asisten virtual {site_name}. Ada yang bisa kami bantu seputar pendaftaran PPDB atau informasi sekolah?", 'type' => 'text', 'group_name' => 'features'],
            ['key' => 'google_analytics_id',   'value' => '', 'type' => 'text', 'group_name' => 'analytics'],
        ];
        foreach ($settings as $s) {
            SiteSetting::updateOrCreate(['key' => $s['key']], $s);
        }

        // 3. Pages
        $pages = [
            [
                'title' => 'Sejarah',
                'slug' => 'sejarah',
                'is_published' => true,
                'content' => <<<'HTML'
                <p>SMA XYZ berdiri pada 17 Juli 1978 di Krian, Sidoarjo. Pendiriannya digagas tokoh masyarakat dan ulama setempat yang ingin menyediakan SMA bernafaskan Islam bagi warga Krian dan sekitarnya.</p>
                <p>Bangunan pertamanya tiga ruang kelas di atas tanah wakaf, dengan 42 siswa dan tujuh guru. Jumlah pendaftar bertambah setiap tahun, sehingga yayasan menambah ruang kelas, masjid, dan asrama tahfidz secara bertahap.</p>
                <p>Tonggak yang menandai perjalanan sekolah:</p>
                <ul>
                    <li>1978: tiga ruang kelas pertama dibuka.</li>
                    <li>1989: masjid kampus dan asrama tahfidz berdiri.</li>
                    <li>2005: akreditasi A pertama diraih.</li>
                    <li>2016: laboratorium komputer dan studio multimedia mulai digunakan.</li>
                    <li>2024: akreditasi A (Unggul) diperbarui.</li>
                </ul>
                <p>Saat ini SMA XYZ menampung 850 siswa dalam 27 rombel. Alumni tersebar di perguruan tinggi negeri, pesantren, dan industri, dan sebagian dari mereka kembali mengajar atau menjadi pembina ekstrakurikuler.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'text', 'content' => <<<'HTML'
                    <p>Kebiasaan yang kami jaga sejak angkatan pertama:</p>
                    <ul>
                        <li>Kurikulum agama ditinjau setiap awal tahun ajaran.</li>
                        <li>Forum MGMP internal digelar setiap Jumat untuk para guru.</li>
                        <li>Laporan keuangan yayasan dibuka untuk orang tua setiap semester.</li>
                    </ul>
                    HTML],
                    ['type' => 'table', 'title' => 'Kepala Sekolah dari Masa ke Masa', 'description' => 'Periode kepemimpinan dan penanda utama pada tiap masa.', 'columns' => ['Nama', 'Periode', 'Penanda'], 'rows' => [
                        ['K.H. Abdullah Syafi\'i', '1978-1992', 'Pendirian gedung induk'],
                        ['Drs. H. Sulaiman Hakim', '1992-2004', 'Masjid kampus dan asrama tahfidz'],
                        ['Dra. Hj. Maimunah Yusuf', '2004-2015', 'Akreditasi A pertama'],
                        ['Dr. H. Ahmad Dahlan, M.Pd.', '2015-sekarang', 'Laboratorium komputer dan kelas digital'],
                    ]],
                ]],
            ],
            [
                'title' => 'Visi, Misi dan Tujuan',
                'slug' => 'visi-misi-tujuan',
                'is_published' => true,
                'content' => <<<'HTML'
                <h3>Visi</h3>
                <p>Terwujudnya generasi rabbani yang berakhlak mulia, unggul dalam IPTEK, berwawasan global, dan berbudaya lingkungan.</p>
                <h3>Misi</h3>
                <ol>
                    <li>Menyelenggarakan pendidikan berbasis karakter Al-Qur'an dan As-Sunnah.</li>
                    <li>Mengembangkan Kurikulum Merdeka yang terhubung dengan teknologi informasi.</li>
                    <li>Membina prestasi akademik dan non-akademik sampai tingkat nasional.</li>
                    <li>Membiasakan bahasa Arab dan Inggris dalam keseharian siswa.</li>
                    <li>Menjaga lingkungan sekolah yang bersih, hijau, dan aman.</li>
                </ol>
                <h3>Tujuan</h3>
                <ul>
                    <li>Lulusan diterima di PTN, perguruan tinggi luar negeri, atau pesantren tahfidz pilihan.</li>
                    <li>Siswa hafal 3-5 juz dengan tajwid yang benar sebelum lulus.</li>
                    <li>Guru punya jalur naik jenjang lewat pelatihan dan sertifikasi.</li>
                </ul>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'text', 'content' => <<<'HTML'
                    <p>Visi ini diterjemahkan menjadi kebiasaan harian di sekolah:</p>
                    <ul>
                        <li>Tadarus Al-Qur'an sebelum jam pertama.</li>
                        <li>Satu proyek lintas mata pelajaran setiap semester.</li>
                        <li>Piket kebersihan kelas dan taman kampus setiap hari.</li>
                    </ul>
                    HTML],
                    ['type' => 'table', 'title' => 'Indikator Ketercapaian Visi', 'description' => 'Diperiksa dalam rapat evaluasi akhir tahun ajaran.', 'columns' => ['Indikator', 'Satuan', 'Target'], 'rows' => [
                        ['Rata-rata nilai rapor', 'Skala 100', '85'],
                        ['Hafalan Al-Qur\'an minimal 3 juz', 'Persen angkatan', '90'],
                        ['Lulusan diterima di PTN', 'Persen angkatan', '70'],
                        ['Guru ikut pelatihan bersertifikat', 'Orang per tahun', '30'],
                    ]],
                ]],
            ],
            [
                'title' => 'Sambutan',
                'slug' => 'sambutan',
                'is_published' => true,
                'content' => <<<'HTML'
                <p>Assalamu'alaikum warahmatullahi wabarakatuh.</p>
                <p>Selamat datang di portal resmi SMA XYZ. Portal ini kami jaga agar orang tua dan calon siswa bisa mengikuti kegiatan sekolah tanpa menunggu pengumuman di papan informasi.</p>
                <p>Kami percaya pendidikan yang baik berdampingan dengan pembinaan ibadah dan kepekaan sosial, tidak berhenti pada nilai akademik. Jam belajar karena itu disandingkan dengan tahfidz, latihan kepemimpinan OSIS, dan kegiatan bakti di lingkungan Krian.</p>
                <p>Fokus kami tahun ini:</p>
                <ul>
                    <li>Pembinaan olimpiade untuk kelas X dan XI.</li>
                    <li>Penambahan jam praktikum di laboratorium IPA.</li>
                    <li>Kelas bahasa Arab aktif untuk kelas XII.</li>
                </ul>
                <p>Bila ada pertanyaan tentang pendaftaran atau layanan sekolah, silakan hubungi sekretariat pada hari kerja.</p>
                <p>Wassalamu'alaikum warahmatullahi wabarakatuh.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'blockquote', 'content' => '<p>Sekolah ini bukan sekadar tempat mengejar nilai. Di sini siswa belajar jujur, disiplin, dan bertanggung jawab atas pilihannya sendiri.</p>', 'source' => 'Dr. H. Ahmad Dahlan, M.Pd., Kepala SMA XYZ'],
                    ['type' => 'callout', 'content' => '<p>Layanan tatap muka: Senin-Jumat, 07.00-15.00 di sekretariat. WhatsApp sekretariat aktif sampai pukul 20.00.</p>'],
                ]],
            ],
            [
                'title' => 'Kerjasama',
                'slug' => 'kerjasama',
                'is_published' => true,
                'content' => <<<'HTML'
                <p>SMA XYZ bekerja sama dengan kampus, lembaga bahasa, dan industri untuk pembinaan siswa serta pelatihan guru. Setiap kerja sama berjalan dengan perjanjian tertulis yang ditinjau sekali setahun.</p>
                <p>Mitra yang berjalan saat ini:</p>
                <ol>
                    <li>Universitas Airlangga (UNAIR): pembinaan olimpiade biologi dan kimia.</li>
                    <li>Institut Teknologi Sepuluh Nopember (ITS): pelatihan coding dan robotika.</li>
                    <li>Markaz Lughah Al-Arabiyah Cairo: sertifikasi bahasa Arab untuk kelas XII.</li>
                    <li>Ruangguru: kelas tambahan daring untuk kelas XII.</li>
                    <li>Bank Syariah Indonesia: beasiswa siswa berprestasi dan kelas literasi keuangan.</li>
                </ol>
                <p>Usulan kerja sama baru diajukan ke bidang Humas dengan proposal berisi ruang lingkup dan jadwal kegiatan.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Mitra dan Ruang Lingkup Kerja Sama', 'description' => 'Ditinjau setiap akhir tahun ajaran bersama bidang Humas.', 'columns' => ['Mitra', 'Bidang', 'Kegiatan'], 'rows' => [
                        ['Universitas Airlangga', 'Pendidikan', 'Pembinaan olimpiade biologi dan kimia'],
                        ['ITS Surabaya', 'Teknologi', 'Pelatihan coding dan robotika'],
                        ['Markaz Lughah Al-Arabiyah', 'Bahasa', 'Sertifikasi bahasa Arab kelas XII'],
                        ['Ruangguru', 'Pendidikan', 'Kelas tambahan daring'],
                        ['Bank Syariah Indonesia', 'Beasiswa', 'Beasiswa berprestasi dan literasi keuangan'],
                    ]],
                    ['type' => 'callout', 'content' => '<p>Pengajuan kerja sama: proposal dikirim ke humas@smaxyz.sch.id, lalu dijadwalkan rapat dengan bidang terkait dalam dua minggu.</p>'],
                ]],
            ],
            [
                'title' => 'Fasilitas',
                'slug' => 'fasilitas',
                'is_published' => true,
                'content' => <<<'HTML'
                <p>SMA XYZ menempati kampus di Jl. Kyai Mojo No. 1, Krian. Ruang kelas ber-AC, masjid dua lantai, perpustakaan, tiga laboratorium, dan lapangan multifungsi dipakai bergiliran sesuai jadwal pelajaran.</p>
                <p>Fasilitas yang paling sering dipakai siswa:</p>
                <ul>
                    <li>Laboratorium Komputer dan Digital Class, 40 unit PC dengan jaringan fiber 500 Mbps.</li>
                    <li>Masjid Kampus Al-Iman berkapasitas 800 jamaah.</li>
                    <li>Perpustakaan Digital dan Media Center dengan koleksi fisik dan akses e-library nasional.</li>
                    <li>Laboratorium IPA Terpadu untuk praktikum Fisika, Kimia, dan Biologi.</li>
                    <li>Lapangan Olahraga Multifungsi dengan lampu untuk latihan malam.</li>
                    <li>Studio Podcast dan Multimedia untuk kelas konten dan siaran sekolah.</li>
                </ul>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Jam Layanan Fasilitas', 'description' => 'Di luar jam ini, pemakaian ruang harus mengajukan izin ke Kepala TU.', 'columns' => ['Fasilitas', 'Hari', 'Jam'], 'rows' => [
                        ['Perpustakaan', 'Senin-Jumat', '07.00-16.00'],
                        ['Laboratorium Komputer', 'Senin-Jumat', '07.00-15.00'],
                        ['Studio Podcast', 'Selasa dan Kamis', '13.00-16.00'],
                        ['Lapangan Olahraga', 'Setiap hari', '06.00-21.00'],
                    ]],
                    ['type' => 'callout', 'content' => '<p>Peminjaman ruang melalui petugas sarana atau Kepala TU. Kerusakan alat dilaporkan ke wali kelas agar segera diperbaiki.</p>'],
                ]],
            ],
            [
                'title' => 'Guru dan Tendik',
                'slug' => 'guru-tendik',
                'is_published' => true,
                'content' => <<<'HTML'
                <p>Tenaga pendidik SMA XYZ berjumlah 45 guru dan 18 tenaga kependidikan. Sebagian besar guru berlatar S1 dan S2 dari UNAIR, ITS, UNESA, dan UINSA, ditambah guru pondok pesantren mitra untuk mapel PAI dan tahfidz.</p>
                <p>Pembagian tugas yang berjalan:</p>
                <ul>
                    <li>Waka Kurikulum mengatur jadwal, asesmen, dan supervisi kelas.</li>
                    <li>Waka Kesiswaan menangani OSIS, ekstrakurikuler, dan kedisiplinan.</li>
                    <li>Waka Humas menjadi pintu masuk kerja sama dan layanan orang tua.</li>
                    <li>Koordinator Tahfidz mengatur setoran hafalan dan wisuda tahfidz.</li>
                    <li>Kepala TU mengelola administrasi, sarana, dan sistem informasi sekolah.</li>
                </ul>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Komposisi Guru dan Tendik', 'description' => 'Data tahun ajaran 2026/2027.', 'columns' => ['Pendidikan Terakhir', 'Guru', 'Tendik'], 'rows' => [
                        ['S2', '12', '1'],
                        ['S1', '33', '9'],
                        ['D3/SMA', '0', '8'],
                    ]],
                    ['type' => 'text', 'content' => <<<'HTML'
                    <p>Jalur pengembangan yang tersedia bagi guru dan tendik:</p>
                    <ul>
                        <li>Pelatihan kurikulum dan asesmen setiap awal semester.</li>
                        <li>Pendampingan guru baru oleh guru senior selama satu tahun.</li>
                        <li>Studi banding ke sekolah atau kampus mitra satu kali setahun.</li>
                    </ul>
                    HTML],
                ]],
            ],
            [
                'title' => 'GTK',
                'slug' => 'gtk',
                'is_published' => true,
                'content' => <<<'HTML'
                <p>Guru dan tenaga kependidikan SMA XYZ bekerja dengan pembagian tugas yang jelas: 45 guru, 18 tenaga kependidikan, dan tim tahfidz dari pondok pesantren mitra.</p>
                <ul>
                    <li>Kualifikasi guru: S1 dan S2 dari UNAIR, ITS, UNESA, dan UINSA.</li>
                    <li>Guru bersertifikat pendidik: 38 orang.</li>
                    <li>Guru tahfidz bersanad: 6 orang hafiz dan hafizah.</li>
                    <li>Tenaga kependidikan: administrasi, perpustakaan, laboratorium, dan sarana prasarana.</li>
                </ul>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Program Pengembangan Tenaga Pendidik', 'description' => 'Jadwal rutin yang berjalan setiap tahun ajaran.', 'columns' => ['Program', 'Peserta', 'Jadwal'], 'rows' => [
                        ['MGMP internal', 'Semua guru mapel', 'Setiap Jumat'],
                        ['Pelatihan Kurikulum Merdeka', 'Guru baru', 'Awal tahun ajaran'],
                        ['Pengurusan sertifikasi pendidik', 'Guru yang memenuhi syarat', 'Sesuai jadwal pemerintah'],
                        ['Pelatihan sistem informasi sekolah', 'Tenaga kependidikan', 'Setiap semester'],
                    ]],
                    ['type' => 'callout', 'content' => '<p>Guru dan tendik baru menjalani orientasi dua pekan sebelum mulai bertugas, termasuk pendampingan wali kelas senior.</p>'],
                ]],
            ],
            [
                'title' => 'Struktur Organisasi',
                'slug' => 'struktur-organisasi',
                'is_published' => true,
                'content' => <<<'HTML'
                <p>Struktur organisasi SMA XYZ berada di bawah Yayasan Pendidikan SMA XYZ. Kepala sekolah membawahi empat wakil, kepala tata usaha, dan koordinator tahfidz.</p>
                <ol>
                    <li>Ketua Yayasan membawahi kepala sekolah.</li>
                    <li>Kepala sekolah memimpin wakil kurikulum, kesiswaan, humas, dan sarana prasarana.</li>
                    <li>Kepala tata usaha menangani administrasi, keuangan harian, dan sistem informasi.</li>
                    <li>Koordinator tahfidz mengatur program hafalan bersama guru PAI.</li>
                </ol>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Pimpinan Sekolah', 'description' => 'Pejabat yang menjabat pada tahun ajaran ini.', 'columns' => ['Jabatan', 'Nama', 'Email'], 'rows' => [
                        ['Kepala Sekolah', 'Dr. H. Ahmad Dahlan, M.Pd.', 'dahlan@smaxyz.sch.id'],
                        ['Waka Kurikulum', 'Rina Wulandari, M.Si.', 'rina@smaxyz.sch.id'],
                        ['Waka Kesiswaan', 'M. Rizky Pratama, S.Pd.', 'rizky@smaxyz.sch.id'],
                        ['Waka Humas', 'Siti Aminah, S.Pd.', 'aminah@smaxyz.sch.id'],
                        ['Kepala Tata Usaha', 'Budi Santoso, S.Kom.', 'budi@smaxyz.sch.id'],
                    ]],
                    ['type' => 'callout', 'content' => '<p>Layanan surat keterangan dan legalisasi: Senin-Jumat, 08.00-14.00 di ruang tata usaha.</p>'],
                ]],
            ],
            [
                'title' => 'Petunjuk Pendaftaran PPDB',
                'slug' => 'petunjuk-ppdb',
                'is_published' => true,
                'content' => <<<'HTML'
                <p>Pendaftaran PPDB SMA XYZ dilakukan daring melalui portal ppdb.smaxyz.sch.id. Berkas yang diunggah diverifikasi tim panitia, dan hasilnya dikirim ke email pendaftar dalam dua hari kerja.</p>
                <ol>
                    <li>Akses portal ppdb.smaxyz.sch.id dan buat akun dengan NIK serta email aktif.</li>
                    <li>Isi formulir data diri, sekolah asal, dan nilai rapor.</li>
                    <li>Unggah berkas: rapor SMP semester 1-5, akta lahir, kartu keluarga, dan sertifikat prestasi bila ada.</li>
                    <li>Pilih jalur pendaftaran: prestasi akademik, tahfidz, atau reguler.</li>
                    <li>Bayar biaya pendaftaran melalui BSI atau transfer bank, lalu simpan bukti bayar.</li>
                    <li>Ikuti tes pemetaan potensi dan wawancara karakter di kampus.</li>
                    <li>Pantau hasil seleksi di portal, lalu lakukan daftar ulang bila dinyatakan diterima.</li>
                </ol>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Gelombang Pendaftaran', 'description' => 'Kuota tiap gelombang terbatas, pendaftar yang menyelesaikan administrasi lebih dulu diprioritaskan saat daftar ulang.', 'columns' => ['Gelombang', 'Periode', 'Catatan'], 'rows' => [
                        ['Gelombang 1', '1 Oktober sampai 31 Desember 2026', 'Potongan biaya pendaftaran'],
                        ['Gelombang 2', '1 Januari sampai 31 Maret 2027', 'Biaya penuh'],
                        ['Gelombang 3', '1 April sampai 30 Juni 2027', 'Sesuai sisa kuota'],
                    ]],
                    ['type' => 'callout', 'content' => '<p>Jalur tahfidz: beasiswa penuh untuk hafalan 3 juz atau lebih dengan surat keterangan dari ustadz pembimbing. Berkas diunggah bersama dokumen lain saat mendaftar.</p>'],
                ]],
            ],
            [
                'title' => 'Program Unggulan Tahfidz',
                'slug' => 'program-tahfidz',
                'is_published' => true,
                'content' => <<<'HTML'
                <p>Program Tahfidzul Qur'an berjalan sejak 1989 dan kini menjadi salah satu jalur pendaftaran favorit. Setoran hafalan digelar sore hari bersama pembimbing hafiz bersanad.</p>
                <p>Rangkaian program selama tiga tahun:</p>
                <ul>
                    <li>Kelas X: tahsin bacaan, kaidah tajwid, dan hafalan satu juz.</li>
                    <li>Kelas XI: penambahan hafalan minimal dua juz dengan ujian juz tiap semester.</li>
                    <li>Kelas XII: penyelesaian target 3-5 juz dan persiapan wisuda tahfidz.</li>
                </ul>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Jadwal Program Tahfidz', 'description' => 'Berjalan sepanjang tahun ajaran, di luar jadwal ujian semester.', 'columns' => ['Kegiatan', 'Hari', 'Jam'], 'rows' => [
                        ['Setoran hafalan harian', 'Senin-Kamis', '14.00-16.00'],
                        ['Tahsin dan tajwid', 'Jumat', '13.00-14.30'],
                        ['Ujian juz', 'Akhir semester', 'Sesuai jadwal panitia'],
                        ['Wisuda tahfidz', 'Sekali setahun', 'April'],
                    ]],
                    ['type' => 'blockquote', 'content' => '<p>Hafalan yang baik bukan yang paling cepat selesai, tapi yang paling rajin diulang.</p>', 'source' => 'Ustadz H. Abdul Samad, Lc., M.H., Koordinator Tahfidz'],
                ]],
            ],
        ];
        foreach ($pages as $p) {
            Page::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // 4. Posts - Berita
        $beritaData = [
            [
                'title' => 'Penerimaan Peserta Didik Baru (PPDB) TA 2026/2027 Resmi Dibuka',
                'slug' => 'ppdb-2026-2027-dibuka',
                'category_id' => $catPengumuman->id,
                'content' => <<<'HTML'
                <p>SMA XYZ membuka Penerimaan Peserta Didik Baru (PPDB) tahun ajaran 2026/2027 untuk gelombang pertama. Pendaftaran berjalan daring melalui portal resmi sampai 31 Desember 2026.</p>
                <p>Empat jalur pendaftaran yang tersedia:</p>
                <ul>
                    <li>Prestasi akademik: nilai rapor semester 1-5 dan sertifikat lomba tingkat kabupaten ke atas.</li>
                    <li>Tahfidz: hafalan minimal 3 juz dengan surat keterangan ustadz pembimbing.</li>
                    <li>Reguler: tes pemetaan potensi dan wawancara karakter.</li>
                    <li>Beasiswa yayasan: bagi pemegang KIP atau siswa berprestasi dari sekolah mitra.</li>
                </ul>
                <p>Pendaftar gelombang pertama mendapat potongan biaya pendaftaran. Rincian biaya, jadwal tes, dan panduan pengisian formulir ada di laman Petunjuk Pendaftaran PPDB.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'callout', 'content' => '<p>Kuota gelombang pertama 160 siswa, dan pendaftaran ditutup lebih awal bila kuota terpenuhi. Sekretariat PPDB melayani pertanyaan pada hari kerja, 07.00-15.00.</p>'],
                    ['type' => 'table', 'title' => 'Jadwal PPDB Gelombang 1', 'description' => 'Tanggal dapat bergeser bila kuota terpenuhi lebih awal.', 'columns' => ['Tahapan', 'Tanggal'], 'rows' => [
                        ['Pendaftaran daring', '1 Oktober sampai 31 Desember 2026'],
                        ['Tes pemetaan dan wawancara', '6-10 Januari 2027'],
                        ['Pengumuman hasil', '13 Januari 2027'],
                        ['Daftar ulang', '14-20 Januari 2027'],
                    ]],
                    ['type' => 'text', 'content' => <<<'HTML'
                    <p>Berkas yang disiapkan sebelum mendaftar:</p>
                    <ul>
                        <li>Scan rapor semester 1-5 dalam satu berkas PDF.</li>
                        <li>Scan akta lahir dan kartu keluarga.</li>
                        <li>Pas foto berwarna 3x4 terbaru.</li>
                        <li>Sertifikat prestasi atau surat keterangan tahfidz bagi pendaftar jalur khusus.</li>
                    </ul>
                    HTML],
                ]],
                'status' => 'published', 'published_at' => now(),
            ],
            [
                'title' => 'Siswa SMA XYZ Raih Medali Emas OSN Matematika & Fisika 2026',
                'slug' => 'emas-osn-matematika-fisika-2026',
                'category_id' => $catBerita->id,
                'content' => <<<'HTML'
                <p>Dua siswa SMA XYZ meraih medali emas Olimpiade Sains Nasional (OSN) tingkat Kabupaten Sidoarjo. Muhammad Farhan dari XI MIPA 1 menempati peringkat pertama bidang Matematika, dan Nabilah Putri dari XII MIPA 2 meraih peringkat pertama bidang Fisika.</p>
                <p>Keduanya dibina sejak Februari lewat bimbingan dua kali sepekan bersama Rina Wulandari, M.Si. dan Eko Prasetyo, S.Pd. Soal latihan diambil dari arsip OSN lima tahun terakhir, lalu dibahas bersama sampai semua langkah pengerjaan jelas.</p>
                <p>Faktor yang disebut pembina sebagai penentu hasil:</p>
                <ul>
                    <li>Kebiasaan mengerjakan satu paket soal setiap pekan sejak kelas X.</li>
                    <li>Diskusi terbuka setelah latihan, termasuk saat jawaban salah.</li>
                    <li>Penyesuaian tenggat tugas dari wali kelas selama masa pembinaan.</li>
                </ul>
                <p>Farhan dan Nabilah mewakili Sidoarjo pada OSN tingkat Provinsi Jawa Timur. Pendampingan berlanjut di ruang multimedia sekolah sampai hari pelaksanaan.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Hasil Delegasi SMA XYZ', 'description' => 'Hasil OSN tingkat Kabupaten Sidoarjo 2026.', 'columns' => ['Bidang', 'Nama', 'Kelas', 'Hasil'], 'rows' => [
                        ['Matematika', 'Muhammad Farhan', 'XI MIPA 1', 'Medali emas'],
                        ['Fisika', 'Nabilah Putri', 'XII MIPA 2', 'Medali emas'],
                    ]],
                    ['type' => 'blockquote', 'content' => '<p>Farhan dan Nabilah bukan siswa yang paling cepat selesai, tapi paling konsisten mengulang soal yang pernah salah.</p>', 'source' => 'Rina Wulandari, M.Si., pembina Matematika'],
                    ['type' => 'callout', 'content' => '<p>Latihan olimpiade tingkat provinsi digelar Selasa dan Kamis, 15.00-17.00 di ruang multimedia. Siswa kelas X dan XI yang berminat bisa mendaftar ke wali kelas.</p>'],
                ]],
                'status' => 'published', 'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Pesantren Kilat Ramadhan 1447 H Berlangsung Khidmat',
                'slug' => 'pesantren-kilat-ramadhan-1447h',
                'category_id' => $catKegiatan->id,
                'content' => <<<'HTML'
                <p>Pesantren Kilat Ramadhan 1447 H digelar lima hari dan diikuti seluruh siswa kelas X dan XI. Materinya disusun bersama guru PAI, koordinator tahfidz, dan pengurus OSIS.</p>
                <p>Kegiatan yang berjalan setiap hari:</p>
                <ul>
                    <li>Kajian fiqih puasa setelah shalat Dzuhur.</li>
                    <li>Tahsin dan setoran hafalan sebelum berbuka.</li>
                    <li>Qiyamul lail berjamaah pada hari keempat.</li>
                    <li>Bakti sosial membagikan takjil ke warga sekitar kampus.</li>
                </ul>
                <p>Penutupan diisi pembagian hadiah untuk kelompok dengan setoran hafalan terbanyak.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'callout', 'content' => '<p>Peserta membawa buku setoran dan hadir sebelum pukul 07.00. Siswa yang sakit melapor ke wali kelas agar tetap menerima catatan materi.</p>'],
                    ['type' => 'blockquote', 'content' => '<p>Yang paling berat bukan bangun untuk qiyamul lail, tapi melanjutkan setoran hafalan tepat setelahnya.</p>', 'source' => 'Ahmad Zainuddin, siswa X Keagamaan'],
                ]],
                'status' => 'published', 'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Workshop Digital Innovation: Belajar React & Laravel',
                'slug' => 'workshop-react-laravel',
                'category_id' => $catBerita->id,
                'content' => <<<'HTML'
                <p>Lab Komputer SMA XYZ menggelar Workshop Web Development bertema <em>Building Modern Web Apps with React &amp; Laravel</em> selama dua hari. Pesertanya 50 siswa Klub Coding dari kelas X sampai XII.</p>
                <p>Materi hari pertama:</p>
                <ul>
                    <li>Dasar komponen React, props, dan state.</li>
                    <li>Latihan membuat halaman daftar siswa.</li>
                </ul>
                <p>Hari kedua diisi routing, penyimpanan data dengan Laravel, dan cara menampilkan data dari API. Sesi terakhir dipakai untuk demo proyek akhir serta tanya jawab soal karier di bidang perangkat lunak.</p>
                <p>Narasumbernya alumni SMA XYZ angkatan 2017 yang kini bekerja sebagai senior software engineer di perusahaan teknologi di Surabaya.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Jadwal Workshop', 'description' => 'Digelar di Lab Komputer 1 dan 2, peserta dibagi dua ruang.', 'columns' => ['Hari', 'Waktu', 'Materi'], 'rows' => [
                        ['Sabtu', '08.00-12.00', 'Komponen React, props, dan state'],
                        ['Sabtu', '13.00-15.30', 'Latihan halaman daftar siswa'],
                        ['Minggu', '08.00-12.00', 'Routing dan API Laravel'],
                        ['Minggu', '13.00-15.00', 'Demo proyek dan tanya jawab'],
                    ]],
                    ['type' => 'callout', 'content' => '<p>Peserta membawa laptop sendiri dan akun GitHub aktif. Siswa yang belum punya laptop bisa memakai komputer lab dengan mendaftar ke pengurus Klub Coding.</p>'],
                    ['type' => 'blockquote', 'content' => '<p>Yang membedakan bukan siapa yang paling cepat paham, tapi siapa yang berani menyerahkan proyek setengah jadi untuk dikritik.</p>', 'source' => 'Narasumber workshop, alumni SMA XYZ angkatan 2017'],
                    ['type' => 'text', 'content' => <<<'HTML'
                    <p>Lanjutan setelah workshop:</p>
                    <ul>
                        <li>Kelas Flutter untuk 20 siswa, dibuka bulan depan.</li>
                        <li>Portal siswa dikerjakan bersama sebagai tugas akhir Klub Coding.</li>
                    </ul>
                    HTML],
                ]],
                'status' => 'published', 'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Tim Futsal SMA XYZ Juara 1 Sidoarjo High School Cup 2026',
                'slug' => 'futsal-juara-1-sidoarjo-cup-2026',
                'category_id' => $catEkstra->id,
                'content' => <<<'HTML'
                <p>Tim Futsal SMA XYZ menjuarai Sidoarjo High School Cup 2026 setelah menundukkan SMAN 1 Krian 3-2 di partai final. Kapten tim, Adi Nugroho, terpilih sebagai pemain terbaik turnamen.</p>
                <p>Perjalanan tim sejak babak penyisihan:</p>
                <ol>
                    <li>Penyisihan grup: menang 4-1 atas SMAN 2 Sidoarjo.</li>
                    <li>Perempat final: menang 2-0 atas SMK Negeri 1 Buduran.</li>
                    <li>Semifinal: menang adu penalti 5-4 atas SMA Muhammadiyah 1.</li>
                    <li>Final: menang 3-2 atas SMAN 1 Krian.</li>
                </ol>
                <p>Latihan rutin Selasa dan Kamis bersama pelatih M. Rizky Pratama, S.Pd. dilanjutkan untuk persiapan kejuaraan provinsi pada November.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'callout', 'content' => '<p>Seleksi anggota tim untuk musim berikutnya dibuka bagi kelas X dan XI. Latihan percobaan digelar dua kali sebelum seleksi resmi.</p>'],
                    ['type' => 'blockquote', 'content' => '<p>Kami kalah di final tahun lalu. Sekarang anak-anak tahu apa yang harus diperbaiki setelah setiap pertandingan, bukan setelah peluit akhir turnamen.</p>', 'source' => 'M. Rizky Pratama, S.Pd., pelatih tim futsal'],
                    ['type' => 'text', 'content' => <<<'HTML'
                    <p>Persiapan menuju kejuaraan provinsi:</p>
                    <ul>
                        <li>Latihan fisik tambahan setiap Senin pagi.</li>
                        <li>Uji coba melawan dua klub lokal akhir bulan ini.</li>
                        <li>Evaluasi video pertandingan setiap Jumat.</li>
                    </ul>
                    HTML],
                ]],
                'status' => 'published', 'published_at' => now()->subDays(15),
            ],
            [
                'title' => 'Pengumuman Pelaksanaan Ulangan Tengah Semester (UTS) Ganjil',
                'slug' => 'pengumuman-uts-ganjil-2026',
                'category_id' => $catPengumuman->id,
                'content' => <<<'HTML'
                <p>Ulangan Tengah Semester (UTS) ganjil tahun ajaran 2026/2027 dilaksanakan 15-22 Oktober 2026 dengan sistem Computer Based Test (CBT). Jadwal mata pelajaran diunggah di portal siswa dan ditempel di papan pengumuman setiap kelas.</p>
                <p>Ketentuan yang berlaku:</p>
                <ol>
                    <li>Kartu ujian diambil di wali kelas mulai Senin, dua hari sebelum jadwal pertama.</li>
                    <li>Siswa hadir 15 menit sebelum sesi dimulai dan membawa kartu ujian.</li>
                    <li>Perangkat dan akun CBT diuji sendiri melalui simulasi pada 13 Oktober.</li>
                    <li>Siswa yang sakit menyerahkan surat keterangan dokter untuk mengikuti ujian susulan.</li>
                </ol>
                <p>Nilai diumumkan paling lambat dua minggu setelah ujian terakhir dan dibagikan pada pertemuan wali murid.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Jadwal UTS per Kelas', 'description' => 'Sesi susulan digelar 23-24 Oktober 2026 setelah jam pelajaran.', 'columns' => ['Hari', 'Kelas X', 'Kelas XI', 'Kelas XII'], 'rows' => [
                        ['Kamis, 15 Oktober', 'Matematika', 'Bahasa Indonesia', 'Matematika'],
                        ['Jumat, 16 Oktober', 'Bahasa Inggris', 'Matematika', 'Bahasa Indonesia'],
                        ['Senin, 19 Oktober', 'Fisika', 'Biologi', 'Fisika'],
                        ['Selasa, 20 Oktober', 'Biologi', 'Kimia', 'Biologi'],
                    ]],
                    ['type' => 'callout', 'content' => '<p>Kartu ujian wajib dibawa pada setiap sesi. Peserta yang lupa membawa kartu tetap boleh mengikuti ujian setelah konfirmasi ke wali kelas.</p>'],
                    ['type' => 'text', 'content' => <<<'HTML'
                    <p>Sebelum ujian dimulai, pastikan hal teknis ini:</p>
                    <ul>
                        <li>Password akun CBT sudah dicoba pada sesi simulasi.</li>
                        <li>Nomor sesi dan ruang ujian dibaca ulang sehari sebelumnya.</li>
                        <li>Komputer lab sudah tertutup dari aplikasi selain CBT.</li>
                    </ul>
                    HTML],
                ]],
                'status' => 'published', 'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Pelajar SMA XYZ Belajar Robotika di Laboratorium ITS Surabaya',
                'slug' => 'kunjungan-robotika-its-surabaya',
                'category_id' => $catBerita->id,
                'content' => <<<'HTML'
                <p>Sebanyak 48 siswa kelas XI MIPA mengikuti kunjungan edukasi ke Laboratorium Robotika dan Mekatronika ITS Surabaya. Kegiatan ini melengkapi kelas teknologi yang berjalan di sekolah setiap pekan.</p>
                <p>Di laboratorium, siswa menyaksikan demonstrasi lengan robot, sistem sensor otomatis, dan cara mahasiswa menyusun prototipe untuk kompetisi. Sesi tanya jawab berlangsung bersama dua asisten laboratorium dan tiga anggota tim robotika kampus.</p>
                <p>Pertanyaan yang paling sering muncul dari siswa:</p>
                <ul>
                    <li>Bagaimana cara menguji sensor sebelum robot dirakit?</li>
                    <li>Bagaimana pembagian tugas tim saat kompetisi berlangsung?</li>
                    <li>Jalur belajar apa yang perlu disiapkan untuk masuk rekayasa robotika?</li>
                </ul>
                <p>Sepulang kunjungan, siswa mendapat tugas merancang robot sederhana berbahan sensor cahaya yang dikumpulkan dua pekan kemudian.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'ANBK 2026: 45 Siswa Kelas XI Ikut Asesmen Literasi dan Numerasi',
                'slug' => 'anbk-2026-siswa-kelas-xi',
                'category_id' => $catBerita->id,
                'content' => <<<'HTML'
                <p>Asesmen Nasional Berbasis Komputer (ANBK) 2026 digelar dua hari di Lab Komputer 1 dan 2. Sebanyak 45 siswa kelas XI terpilih sebagai sampel mengerjakan soal literasi membaca, numerasi, dan survei karakter.</p>
                <p>Pelaksanaan dibagi dua sesi setiap hari dengan pendampingan proktor dan teknisi sekolah. Pengawas silang datang dari sekolah lain sesuai jadwal dari dinas pendidikan.</p>
                <p>Hasil asesmen dipakai sekolah untuk membaca mutu pembelajaran, terutama pola jawaban numerasi yang paling sering salah. Rangkumannya dibahas dalam rapat kurikulum bersama seluruh guru mapel.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Bimbingan Karir Kelas XII: Memilih Jurusan Sesuai Minat dan Peluang',
                'slug' => 'bimbingan-karir-kelas-xii',
                'category_id' => $catBerita->id,
                'content' => <<<'HTML'
                <p>Seluruh siswa kelas XII mengikuti bimbingan karir di Aula Utama bersama empat alumni yang kini berkuliah di UNAIR, ITS, dan Al-Azhar Kairo, serta satu praktisi dari industri teknologi Surabaya.</p>
                <p>Alumni berbagi cara memilih jurusan: mengenali mata pelajaran yang paling dikuasai, memeriksa peluang beasiswa di tiap kampus, dan menyusun strategi belajar untuk SNBT. Sesi terakhir dipakai untuk konsultasi kelompok kecil.</p>
                <p>Tiga hal yang ditekankan berulang kali dalam tanya jawab:</p>
                <ul>
                    <li>Nilai rapor tetap fondasi, tetapi portofolio kegiatan juga dihitung pada jalur seleksi tertentu.</li>
                    <li>Setiap siswa perlu menyiapkan kampus cadangan dengan alasan yang dipahami sendiri.</li>
                    <li>Latihan soal dimulai dari sekarang, bukan setelah pendaftaran dibuka.</li>
                </ul>
                <p>Guru bimbingan konseling membuka sesi lanjutan setiap Rabu setelah jam pelajaran untuk membahas pilihan masing-masing siswa.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Perpustakaan Tambah 480 Koleksi Baru untuk Semester Ganjil',
                'slug' => 'perpustakaan-tambah-480-koleksi',
                'category_id' => $catBerita->id,
                'content' => <<<'HTML'
                <p>Perpustakaan Digital dan Media Center menambah 480 koleksi baru: buku referensi sains, novel remaja, biografi tokoh Muslim, dan buku keterampilan. Sebagian judul dipilih dari usulan siswa lewat kotak saran perpustakaan.</p>
                <p>Pengadaan berlangsung sejak awal semester. Sebanyak 120 di antaranya judul baru, sisanya salinan tambahan untuk judul favorit yang selalu habis dipinjam.</p>
                <p>Aturan peminjaman juga disederhanakan:</p>
                <ul>
                    <li>Anggota bisa meminjam dua buku sekaligus untuk masa pinjam satu pekan.</li>
                    <li>Katalog digital diperbarui sehingga siswa bisa memeriksa ketersediaan judul sebelum ke sekolah.</li>
                    <li>Perpanjangan pinjaman dapat dilakukan lewat petugas perpustakaan.</li>
                </ul>
                <p>Lomba resensi buku digelar akhir bulan sebagai bagian dari Pekan Literasi Sekolah.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'Pelatihan Jurnalistik: Siswa Belajar Menulis dan Memverifikasi Berita',
                'slug' => 'pelatihan-jurnalistik-siswa',
                'category_id' => $catBerita->id,
                'content' => <<<'HTML'
                <p>Sebanyak 30 anggota tim majalah sekolah dan pengurus OSIS mengikuti pelatihan jurnalistik bersama pemimpin redaksi sebuah media lokal di Sidoarjo. Pelatihan berlangsung satu hari di ruang multimedia.</p>
                <p>Materi pagi membahas cara menemukan sudut berita kegiatan sekolah, menyusun pertanyaan wawancara, dan menulis berita pendek. Sesi siang diisi praktik: setiap tim mewawancarai narasumber di lingkungan sekolah lalu menuliskan hasilnya.</p>
                <p>Narasumber menekankan tiga aturan yang selalu diuji di dunia jurnalistik:</p>
                <ul>
                    <li>Periksa satu informasi dari minimal dua sumber sebelum ditulis.</li>
                    <li>Cantumkan identitas narasumber dengan jabatan yang benar.</li>
                    <li>Jangan mengubah kutipan tanpa sepengetahuan narasumber.</li>
                </ul>
                <p>Hasil pelatihan langsung dipakai untuk menyiapkan majalah sekolah edisi berikutnya.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Rapat Kerja Semester Ganjil Tetapkan Empat Prioritas Program',
                'slug' => 'raker-semester-ganjil',
                'category_id' => $catBerita->id,
                'content' => <<<'HTML'
                <p>Rapat kerja semester ganjil digelar dua hari di aula sekolah, diikuti kepala sekolah, para wakil, guru, dan perwakilan pengurus yayasan. Rapat menyepakati empat prioritas yang dijalankan sampai akhir tahun ajaran.</p>
                <ol>
                    <li>Penguatan literasi dan numerasi lewat asesmen seragam setiap bulan.</li>
                    <li>Pembinaan olimpiade berjenjang mulai kelas X.</li>
                    <li>Digitalisasi administrasi dan layanan orang tua lewat portal sekolah.</li>
                    <li>Pembinaan karakter bersama wali kelas dan guru tahfidz.</li>
                </ol>
                <p>Setiap prioritas memiliki penanggung jawab dan tenggat evaluasi. Progresnya dibahas dalam rapat bulanan pimpinan dan dilaporkan ke orang tua pada pertemuan wali murid.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(9),
            ],
            [
                'title' => 'SMA XYZ Berbagi Praktik Sistem Informasi Sekolah dengan Tim SMAN 1 Gresik',
                'slug' => 'studi-tiru-sman-1-gresik',
                'category_id' => $catBerita->id,
                'content' => <<<'HTML'
                <p>Tim SMAN 1 Gresik berkunjung untuk mempelajari pengelolaan sistem informasi sekolah yang berjalan di SMA XYZ. Rombongan berjumlah 12 orang, terdiri atas kepala sekolah, wakil kepala, dan staf tata usaha.</p>
                <p>Diskusi di ruang rapat membahas alur penerbitan konten portal sekolah, penatausahaan data siswa, dan pemanfaatan laboratorium komputer untuk asesmen daring. Tim tamu juga melihat proses pengelolaan konten di ruang multimedia.</p>
                <p>Kunjungan dibalas bulan depan: tim SMA XYZ akan belajar pengelolaan program kewirausahaan siswa ke SMAN 1 Gresik dalam agenda studi tiru balik.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(11),
            ],
            [
                'title' => 'Kantin Sekolah Raih Sertifikat Laik Higiene Sanitasi',
                'slug' => 'kantin-sertifikat-laik-higiene',
                'category_id' => $catBerita->id,
                'content' => <<<'HTML'
                <p>Kantin SMA XYZ menerima sertifikat laik higiene sanitasi dari Puskesmas Krian setelah melalui pemeriksaan dapur, area penyajian, dan sampel menu. Sertifikat berlaku tiga tahun dan diperbarui lewat pemeriksaan berkala.</p>
                <p>Petugas menilai kebersihan alat masak, penyimpanan bahan, kualitas air, hingga penanganan sampah. Para pengelola kantin mengikuti penyuluhan keamanan pangan sehari sebelum penilaian.</p>
                <p>Ke depan, sekolah dan pengelola kantin menetapkan pemeriksaan kebersihan setiap Jumat serta uji sampel makanan bergilir untuk seluruh tenant.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(13),
            ],
            [
                'title' => 'Pendaftaran Beasiswa Prestasi Yayasan Semester Genap Dibuka',
                'slug' => 'beasiswa-prestasi-semester-genap',
                'category_id' => $catPengumuman->id,
                'content' => <<<'HTML'
                <p>Pendaftaran beasiswa prestasi yayasan untuk semester genap dibuka sampai 14 November 2026. Beasiswa diberikan dalam bentuk potongan biaya pendidikan satu semester bagi siswa berprestasi akademik maupun non-akademik.</p>
                <p>Syarat yang harus dipenuhi pendaftar:</p>
                <ol>
                    <li>Rata-rata nilai rapor semester terakhir minimal 85.</li>
                    <li>Hafalan Al-Qur'an minimal dua juz dengan keterangan pembimbing tahfidz.</li>
                    <li>Aktif dalam minimal satu ekstrakurikuler atau organisasi sekolah.</li>
                    <li>Tidak pernah menerima sanksi kedisiplinan berat.</li>
                </ol>
                <p>Berkas diserahkan ke ruang tata usaha dalam satu map: fotokopi rapor, surat keterangan prestasi, dan formulir yang diambil dari wali kelas. Hasil seleksi diumumkan pada 25 November 2026 melalui portal sekolah.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Kalender Libur dan Kegiatan Akhir Semester Ganjil 2026/2027',
                'slug' => 'kalender-akhir-semester-ganjil',
                'category_id' => $catPengumuman->id,
                'content' => <<<'HTML'
                <p>Jadwal akhir semester ganjil sudah ditetapkan. Kegiatan belajar efektif berakhir 28 November 2026, dilanjutkan pekan ujian dan pembagian rapor sesuai kalender berikut.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'table', 'title' => 'Kalender Akhir Semester Ganjil', 'description' => 'Tanggal dapat berubah bila ada penyesuaian dari dinas pendidikan.', 'columns' => ['Kegiatan', 'Tanggal'], 'rows' => [
                        ['Pekan ujian akhir semester', '1-9 Desember 2026'],
                        ['Class meeting dan pekan kreativitas', '10-15 Desember 2026'],
                        ['Pembagian rapor semester ganjil', '20 Desember 2026'],
                        ['Libur semester ganjil', '21 Desember 2026 - 5 Januari 2027'],
                        ['Awal semester genap', '6 Januari 2027'],
                    ]],
                    ['type' => 'callout', 'content' => '<p>Selama libur, portal siswa dan perpustakaan digital tetap dapat diakses dari rumah.</p>'],
                ]],
                'status' => 'published', 'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Pendaftaran Anggota Baru Ekstrakurikuler Gelombang Kedua',
                'slug' => 'pendaftaran-ekstrakurikuler-gelombang-2',
                'category_id' => $catPengumuman->id,
                'content' => <<<'HTML'
                <p>Pendaftaran anggota baru ekstrakurikuler gelombang kedua dibuka sampai 20 Oktober 2026. Kuota tersedia untuk Pramuka, PMR, Robotika, English Club, KIR, Nasyid, Futsal, Basket, dan Voli.</p>
                <p>Pendaftaran melalui wali kelas dengan mengisi formulir berisi maksimal dua pilihan ekstrakurikuler. Latihan percobaan berjalan dua pekan sebelum daftar anggota final ditetapkan pembina.</p>
                <p>Sebagian ekstrakurikuler menetapkan seleksi karena keterbatasan alat, terutama Robotika dan Basket. Jadwal seleksinya diumumkan menyusul lewat portal siswa dan grup kelas.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Jadwal Apel Senin dan Piket Kelas Semester Ganjil',
                'slug' => 'jadwal-apel-piket-semester-ganjil',
                'category_id' => $catPengumuman->id,
                'content' => <<<'HTML'
                <p>Mulai pekan ini, apel Senin pagi dilaksanakan pukul 06.50 di lapangan multifungsi sebelum jam pertama. Kelas yang bertugas menyiapkan pengeras suara, teks doa, dan petugas upacara.</p>
                <p>Roster petugas disusun bergilir per pekan oleh pengurus OSIS bersama waka kesiswaan. Kelas yang bertugas berlatih setiap Jumat sore didampingi pelatih dari pengurus OSIS.</p>
                <p>Daftar piket harian kelas juga diperbarui:</p>
                <ul>
                    <li>Kebersihan kelas dan teras diselesaikan sebelum jam pertama.</li>
                    <li>Sampah dipisah organik dan anorganik, lalu diantar ke bank sampah sekolah.</li>
                    <li>Barang hilang dilaporkan ke sekretariat OSIS untuk dicatat.</li>
                </ul>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(8),
            ],
            [
                'title' => 'Seleksi Tim Olimpiade Sains Sekolah Periode 2027 Dibuka',
                'slug' => 'seleksi-tim-olimpiade-sains-2027',
                'category_id' => $catPengumuman->id,
                'content' => <<<'HTML'
                <p>Seleksi tim olimpiade sains sekolah dibuka bagi siswa kelas X dan XI untuk sembilan bidang: Matematika, Fisika, Kimia, Biologi, Informatika, Astronomi, Kebumian, Geografi, dan Ekonomi.</p>
                <p>Tes seleksi berlangsung 16 November 2026 di ruang kelas XI. Siswa yang lolos mengikuti pembinaan dua kali sepekan bersama guru pembina dan alumni olimpiade.</p>
                <p>Pendaftaran melalui guru mapel masing-masing atau langsung ke ruang kurikulum. Peserta diharapkan memilih maksimal dua bidang agar latihan tetap fokus.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'callout', 'content' => '<p>Jadwal pembinaan perdana diumumkan setelah hasil seleksi keluar. Pembinaan digelar Selasa dan Kamis, pukul 15.00-17.00.</p>'],
                ]],
                'status' => 'published', 'published_at' => now()->subDays(12),
            ],
            [
                'title' => 'Tata Tertib Praktikum di Laboratorium IPA dan Komputer',
                'slug' => 'tata-tertib-praktikum-laboratorium',
                'category_id' => $catPengumuman->id,
                'content' => <<<'HTML'
                <p>Menjelang pekan praktikum terpadu, sekolah menegaskan tata tertib penggunaan laboratorium bagi seluruh siswa. Aturan ini berlaku di Laboratorium IPA Terpadu, Laboratorium Komputer, dan Studio Multimedia.</p>
                <ol>
                    <li>Masuk laboratorium hanya bersama guru atau laboran, tanpa jaket dan tas di meja praktik.</li>
                    <li>Dilarang makan dan minum selama praktikum berlangsung.</li>
                    <li>Alat yang rusak dilaporkan ke laboran, bukan dikembalikan diam-diam ke rak.</li>
                    <li>Komputer laboratorium hanya dipakai untuk keperluan praktikum.</li>
                </ol>
                <p>Siswa yang melanggar aturan membantu laboran merapikan alat setelah jam sekolah. Kerusakan akibat kelalaian ditangani bersama wali kelas dan orang tua.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(14),
            ],
            [
                'title' => 'Jadwal Pemilihan Ketua OSIS Periode 2026/2027',
                'slug' => 'pemilihan-ketua-osis-2026-2027',
                'category_id' => $catPengumuman->id,
                'content' => <<<'HTML'
                <p>Pemilihan ketua dan wakil ketua OSIS periode 2026/2027 digelar pekan depan. Empat pasangan calon dari kelas X dan XI sudah lolos verifikasi berkas dan menyelesaikan masa kampanye tertulis.</p>
                <ol>
                    <li>Senin: penyampaian visi misi dan tanya jawab di aula utama.</li>
                    <li>Selasa: pemungutan suara di bilik yang disiapkan di koridor kelas X.</li>
                    <li>Rabu: penghitungan suara terbuka dan penetapan pemenang.</li>
                </ol>
                <p>Hak pilih dimiliki seluruh siswa dan guru dengan satu suara. Pasangan terpilih dilantik pada apel pekan berikutnya, lalu mengikuti LDKS bersama pembina OSIS.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(18),
            ],
            [
                'title' => 'Program Bimbingan Belajar Gratis Kelas XII Dimulai Pekan Depan',
                'slug' => 'bimbingan-belajar-gratis-kelas-xii',
                'category_id' => $catPengumuman->id,
                'content' => <<<'HTML'
                <p>Sekolah membuka bimbingan belajar gratis untuk kelas XII sebagai persiapan SNBT dan ujian sekolah. Kelas berjalan lima kali sepekan, Senin sampai Jumat, pukul 14.00-16.00 di ruang kelas XI MIPA 1 dan 2.</p>
                <p>Mata pelajaran yang dibimbing: Matematika, Bahasa Indonesia, Bahasa Inggris, dan Penalaran Umum. Pengajarnya guru mapel sekolah dan dua alumni yang kini berkuliah di ITS dan UNAIR.</p>
                <p>Pendaftaran tanpa biaya melalui wali kelas sampai 18 Oktober 2026. Kapasitas 80 siswa per angkatan dan tersedia daftar tunggu bila kuota penuh.</p>
                HTML,
                'metadata' => ['blocks' => [
                    ['type' => 'callout', 'content' => '<p>Peserta membawa buku latihan sendiri. Hasil try out internal setiap dua pekan dikumpulkan untuk dievaluasi pembina.</p>'],
                ]],
                'status' => 'published', 'published_at' => now()->subDays(20),
            ],
        ];

        // 5. Posts - Agenda (dengan metadata event_date & location)
        $agendaData = [
            [
                'title' => 'Pertemuan Wali Murid & Pembagian Lapor Mid Semester',
                'slug' => 'pertemuan-wali-murid-mid-semester',
                'category_id' => $catAgenda->id,
                'content' => <<<'HTML'
                <p>Pertemuan wali murid kelas X sampai XII untuk menerima laporan hasil belajar pertengahan semester ganjil. Agendanya sosialisasi perkembangan belajar, penyerahan laporan, dan konsultasi dengan wali kelas.</p>
                <p>Orang tua diharapkan datang tepat waktu karena sesi konsultasi dibagi per kelas.</p>
                HTML,
                'metadata' => [
                    'event_date' => now()->addDays(7)->toDateString(),
                    'location' => 'Aula Utama SMA XYZ',
                    'blocks' => [
                        ['type' => 'table', 'title' => 'Rundown Pertemuan', 'description' => 'Waktu dapat bergeser mengikuti jumlah orang tua yang hadir.', 'columns' => ['Waktu', 'Agenda', 'Penanggung Jawab'], 'rows' => [
                            ['07.30-08.00', 'Registrasi dan pengambilan nomor kelas', 'Panitia OSIS'],
                            ['08.00-08.30', 'Sambutan kepala sekolah', 'Dr. H. Ahmad Dahlan, M.Pd.'],
                            ['08.30-10.00', 'Penyerahan laporan hasil belajar', 'Wali kelas'],
                            ['10.00-10.30', 'Konsultasi dan tanya jawab', 'Waka Kurikulum'],
                        ]],
                        ['type' => 'callout', 'content' => '<p>Orang tua yang tidak bisa hadir dapat mengambil laporan di ruang wali kelas pada hari kerja berikutnya, pukul 08.00-14.00.</p>'],
                    ],
                ],
                'status' => 'published', 'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Ulangan Tengah Semester (UTS) Ganjil 2026',
                'slug' => 'agenda-uts-ganjil-2026',
                'category_id' => $catAgenda->id,
                'content' => 'Pelaksanaan ujian tertulis dan praktikum CBT untuk seluruh kelas X, XI, dan XII.',
                'metadata' => ['event_date' => now()->addDays(14)->toDateString(), 'location' => 'Ruang Ujian CBT SMA XYZ'],
                'status' => 'published', 'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Peringatan Hari Santri Nasional & Pawai Taaruf',
                'slug' => 'hari-santri-nasional-2026',
                'category_id' => $catAgenda->id,
                'content' => 'Upacara bendera peringatan Hari Santri dilanjutkan pawai taaruf keliling Krian.',
                'metadata' => ['event_date' => now()->addDays(25)->toDateString(), 'location' => 'Lapangan Utama SMA XYZ'],
                'status' => 'published', 'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Try Out Ujian Mandiri & SNBT 2027 Serentak',
                'slug' => 'tryout-snbt-2027',
                'category_id' => $catAgenda->id,
                'content' => <<<'HTML'
                <p>Simulasi ujian masuk PTN bagi seluruh siswa kelas XII, bekerja sama dengan lembaga bimbingan belajar nasional. Hasil try out dipakai untuk memetakan kesiapan siswa pada setiap subtes.</p>
                <p>Nilai dan pembahasannya dibagikan ke siswa dua pekan setelah pelaksanaan.</p>
                HTML,
                'metadata' => [
                    'event_date' => now()->addDays(35)->toDateString(),
                    'location' => 'Lab Komputer 1, 2, dan 3',
                    'blocks' => [
                        ['type' => 'text', 'content' => <<<'HTML'
                        <p>Subtes yang diujikan mengikuti pola SNBT:</p>
                        <ul>
                            <li>Penalaran umum.</li>
                            <li>Pengetahuan dan pemahaman umum.</li>
                            <li>Pemahaman bacaan dan menulis.</li>
                            <li>Pengetahuan kuantitatif.</li>
                            <li>Literasi bahasa Indonesia dan bahasa Inggris.</li>
                            <li>Penalaran matematika.</li>
                        </ul>
                        HTML],
                        ['type' => 'callout', 'content' => '<p>Peserta membawa kartu peserta dan memakai akun try out sekolah. Sesi dibagi tiga gelombang, satu gelombang 40 siswa.</p>'],
                    ],
                ],
                'status' => 'published', 'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Pentas Seni & Pameran Karya Kewirausahaan Siswa',
                'slug' => 'pensi-pameran-karya-2026',
                'category_id' => $catAgenda->id,
                'content' => 'Ajang kreativitas siswa menampilkan seni budaya Islam, bazar kewirausahaan, dan pameran produk digital.',
                'metadata' => ['event_date' => now()->addDays(50)->toDateString(), 'location' => 'Halaman Kampus SMA XYZ'],
                'status' => 'published', 'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Kunjungan Industri Kelas XI ke PT Petrokimia Gresik',
                'slug' => 'kunjungan-industri-petrokimia-gresik',
                'category_id' => $catAgenda->id,
                'content' => <<<'HTML'
                <p>Kunjungan industri tahun ini mengunjungi PT Petrokimia Gresik untuk melihat proses produksi pupuk dan pengelolaan laboratorium kendali mutu. Kegiatan diikuti siswa kelas XI MIPA dan IPS.</p>
                <p>Siswa dibagi tiga kelompok besar agar tur pabrik berjalan tertib. Sebelum berangkat, setiap peserta mengikuti pengarahan keselamatan dan uji pemakaian alat pelindung diri.</p>
                HTML,
                'metadata' => [
                    'event_date' => now()->addDays(30)->toDateString(),
                    'location' => 'PT Petrokimia Gresik',
                    'blocks' => [
                        ['type' => 'callout', 'content' => '<p>Peserta wajib membawa kartu pelajar, memakai sepatu bersol karet, dan tidak diperkenankan membawa tas besar ke area pabrik.</p>'],
                    ],
                ],
                'status' => 'published', 'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Musabaqah Hifzil Qur\'an (MHQ) Cup Antar Kelas',
                'slug' => 'mhq-cup-antar-kelas',
                'category_id' => $catAgenda->id,
                'content' => <<<'HTML'
                <p>MHQ Cup antar kelas mempertandingkan hafalan juz 30 untuk kelas X dan satu juz pilihan untuk kelas XI dan XII. Setiap kelas mengirim dua peserta putra dan dua peserta putri.</p>
                <p>Penilaian mencakup kelancaran, tajwid, dan adab peserta. Dewan juri terdiri atas guru tahfidz dan pembimbing dari pondok pesantren mitra.</p>
                HTML,
                'metadata' => [
                    'event_date' => now()->addDays(45)->toDateString(),
                    'location' => 'Masjid Kampus Al-Iman',
                    'blocks' => [
                        ['type' => 'table', 'title' => 'Babak MHQ Cup', 'description' => 'Peserta yang lolos babak penyisihan tampil ulang di babak final.', 'columns' => ['Babak', 'Waktu', 'Keterangan'], 'rows' => [
                            ['Penyisihan', '07.30-10.00', 'Setoran juz 30 dan juz pilihan'],
                            ['Final', '10.30-12.00', 'Tampil di depan juri dan peserta lain'],
                        ]],
                    ],
                ],
                'status' => 'published', 'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Latihan Dasar Kepemimpinan Siswa untuk Pengurus OSIS Baru',
                'slug' => 'ldks-osis-2026',
                'category_id' => $catAgenda->id,
                'content' => <<<'HTML'
                <p>LDKS menjadi pembekalan wajib bagi pengurus OSIS periode 2026/2027 sebelum menjalankan program kerja. Kegiatan berlangsung dua hari dengan materi kepemimpinan, manajemen kegiatan, dan komunikasi organisasi.</p>
                <p>Materi disampaikan waka kesiswaan, pembina OSIS, dan alumni pengurus OSIS. Hari kedua diisi simulasi penyusunan proposal kegiatan kelompok.</p>
                HTML,
                'metadata' => ['event_date' => now()->addDays(40)->toDateString(), 'location' => 'Aula Utama SMA XYZ'],
                'status' => 'published', 'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Pemeriksaan Kesehatan Berkala Siswa Bersama Puskesmas Krian',
                'slug' => 'pemeriksaan-kesehatan-berkala',
                'category_id' => $catAgenda->id,
                'content' => <<<'HTML'
                <p>Pemeriksaan kesehatan berkala digelar untuk seluruh siswa kelas X, berlanjut ke kelas XI pekan berikutnya. Pemeriksaannya meliputi tinggi badan, berat badan, tekanan darah, kesehatan mata, dan pemeriksaan gigi dasar.</p>
                <p>Hasil pemeriksaan dicatat pada kartu kesehatan siswa. Siswa yang memerlukan pemeriksaan lanjutan dirujuk ke Puskesmas dengan surat pengantar sekolah.</p>
                HTML,
                'metadata' => [
                    'event_date' => now()->addDays(21)->toDateString(),
                    'location' => 'Ruang UKS & Aula SMA XYZ',
                    'blocks' => [
                        ['type' => 'callout', 'content' => '<p>Jadwal per kelas ditempel di papan pengumuman. Siswa hadir sesuai urutan kelas dan membawa kartu kesehatan yang dibagikan wali kelas.</p>'],
                    ],
                ],
                'status' => 'published', 'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Wisuda Tahfidz Angkatan ke-16',
                'slug' => 'wisuda-tahfidz-angkatan-16',
                'category_id' => $catAgenda->id,
                'content' => <<<'HTML'
                <p>Wisuda Tahfidz angkatan ke-16 menampilkan 58 siswa yang menyelesaikan target hafalan 3-5 juz. Acara dihadiri orang tua wisudawan, pengurus yayasan, dan pembimbing pondok pesantren mitra.</p>
                <p>Rangkaian acara: tasmi' bersama, sambutan kepala sekolah, penyerahan piagam, dan penampilan tim nasyid sekolah.</p>
                HTML,
                'metadata' => ['event_date' => now()->addDays(60)->toDateString(), 'location' => 'Masjid Kampus Al-Iman'],
                'status' => 'published', 'published_at' => now()->subDays(5),
            ],
        ];

        // 6. Posts - Prestasi (dengan metadata rank, year, participant_name)
        $prestasiData = [
            [
                'title' => 'Juara 1 OSN Matematika Tingkat Kabupaten Sidoarjo',
                'slug' => 'juara-1-osn-matematika-sidoarjo-2026',
                'category_id' => $catPrestasi->id,
                'content' => 'Muhammad Farhan (XI MIPA 1) meraih nilai tertinggi pada ajang OSN Kabupaten bidang Matematika dan lolos mewakili Sidoarjo ke tingkat Provinsi.',
                'metadata' => ['participant_name' => 'Muhammad Farhan (XI MIPA 1)', 'rank' => 'Juara 1 (Emas)', 'year' => 2026],
                'status' => 'published', 'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Juara 1 OSN Fisika Tingkat Kabupaten Sidoarjo',
                'slug' => 'juara-1-osn-fisika-sidoarjo-2026',
                'category_id' => $catPrestasi->id,
                'content' => 'Nabilah Putri (XII MIPA 2) lolos ke tingkat Provinsi Jawa Timur mewakili Kabupaten Sidoarjo bidang Fisika.',
                'metadata' => ['participant_name' => 'Nabilah Putri (XII MIPA 2)', 'rank' => 'Juara 1 (Emas)', 'year' => 2026],
                'status' => 'published', 'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Juara 2 MTQ Jawa Timur Cabang Mujawwad',
                'slug' => 'juara-2-mtq-jawa-timur-2026',
                'category_id' => $catPrestasi->id,
                'content' => 'Ahmad Zainuddin (X Keagamaan) meraih Juara 2 Musabaqah Tilawatil Qur\'an Provinsi Jawa Timur cabang Mujawwad.',
                'metadata' => ['participant_name' => 'Ahmad Zainuddin (X Keagamaan)', 'rank' => 'Juara 2 (Perak)', 'year' => 2026],
                'status' => 'published', 'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Juara 1 Futsal Sidoarjo High School Cup 2026',
                'slug' => 'prestasi-futsal-sidoarjo-cup-2026',
                'category_id' => $catPrestasi->id,
                'content' => 'Tim Futsal SMA XYZ menjuarai turnamen futsal antar SMA se-Kabupaten Sidoarjo.',
                'metadata' => ['participant_name' => 'Tim Futsal SMA XYZ', 'rank' => 'Juara 1', 'year' => 2026],
                'status' => 'published', 'published_at' => now()->subDays(15),
            ],
            [
                'title' => 'Juara 3 Robotika Line Follower Tingkat Nasional (ITS)',
                'slug' => 'juara-3-robotika-its-2025',
                'category_id' => $catPrestasi->id,
                'content' => 'Dimas Prayoga & Rizky Ramadhan meraih Juara 3 dalam kompetisi robotika otomatis yang diselenggarakan ITS Surabaya.',
                'metadata' => ['participant_name' => 'Dimas Prayoga & Rizky Ramadhan', 'rank' => 'Juara 3 (Perunggu)', 'year' => 2025],
                'status' => 'published', 'published_at' => now()->subDays(120),
            ],
            [
                'title' => 'Juara 1 Lomba Pidato Bahasa Arab Se-Jawa Timur',
                'slug' => 'juara-1-pidato-arab-jatim-2025',
                'category_id' => $catPrestasi->id,
                'content' => 'Aisyah Az-Zahra (XI IPS 1) meraih Juara 1 Lomba Pidato Bahasa Arab Se-Jawa Timur dengan tema Peran Pemuda Islam di Era Digital.',
                'metadata' => ['participant_name' => 'Aisyah Az-Zahra (XI IPS 1)', 'rank' => 'Juara 1', 'year' => 2025],
                'status' => 'published', 'published_at' => now()->subDays(130),
            ],
            [
                'title' => 'Juara 2 Debat Bahasa Inggris Tingkat Provinsi Jawa Timur',
                'slug' => 'juara-2-debat-bahasa-inggris-2026',
                'category_id' => $catPrestasi->id,
                'content' => 'Aqila Nafisah (XI MIPA 3) meraih Juara 2 Lomba Debat Bahasa Inggris Pelajar Se-Jawa Timur, sekaligus terpilih sebagai speaker terbaik kedua pada babak final.',
                'metadata' => ['participant_name' => 'Aqila Nafisah (XI MIPA 3)', 'rank' => 'Juara 2', 'year' => 2026],
                'status' => 'published', 'published_at' => now()->subDays(20),
            ],
            [
                'title' => 'Juara 1 Lomba Karya Tulis Ilmiah Remaja Tingkat Kabupaten Sidoarjo',
                'slug' => 'juara-1-ktir-sidoarjo-2026',
                'category_id' => $catPrestasi->id,
                'content' => 'Nafisa Rahma dan Bima Aryo (XI MIPA 1) meraih Juara 1 dengan karya berjudul Pemanfaatan Kulit Kopi sebagai Bahan Penjernih Air Sumur.',
                'metadata' => ['participant_name' => 'Nafisa Rahma & Bima Aryo (XI MIPA 1)', 'rank' => 'Juara 1', 'year' => 2026],
                'status' => 'published', 'published_at' => now()->subDays(25),
            ],
            [
                'title' => 'Juara 3 Kejuaraan Pencak Silat Pagar Nusa Jawa Timur',
                'slug' => 'juara-3-pencak-silat-jatim-2026',
                'category_id' => $catPrestasi->id,
                'content' => 'Zaki Mubarok (XII IPS 2) meraih Juara 3 kelas tanding putra pada kejuaraan Pagar Nusa tingkat Provinsi Jawa Timur di Surabaya.',
                'metadata' => ['participant_name' => 'Zaki Mubarok (XII IPS 2)', 'rank' => 'Juara 3 (Perunggu)', 'year' => 2026],
                'status' => 'published', 'published_at' => now()->subDays(45),
            ],
            [
                'title' => 'Juara 1 Musabaqah Hifzil Qur\'an 5 Juz Tingkat Kabupaten',
                'slug' => 'juara-1-mhq-kabupaten-2026',
                'category_id' => $catPrestasi->id,
                'content' => 'Salsabila Putri (X Keagamaan) meraih Juara 1 MHQ 5 juz tingkat Kabupaten Sidoarjo, dinilai dari kelancaran, tajwid, dan adab.',
                'metadata' => ['participant_name' => 'Salsabila Putri (X Keagamaan)', 'rank' => 'Juara 1', 'year' => 2026],
                'status' => 'published', 'published_at' => now()->subDays(60),
            ],
        ];

        // Posts - Kegiatan Sekolah
        $kegiatanData = [
            [
                'title' => 'MPLS 2026: Siswa Baru Kenal Sekolah Lewat Lima Hari Kegiatan',
                'slug' => 'mpls-2026',
                'category_id' => $catKegiatan->id,
                'content' => <<<'HTML'
                <p>Masa Pengenalan Lingkungan Sekolah (MPLS) diikuti 320 siswa baru selama lima hari. Kegiatannya berisi pengenalan kurikulum, tata tertib, fasilitas kampus, dan program tahfidz, tanpa perpeloncoan sesuai aturan dinas.</p>
                <p>Setiap kelompok didampingi dua pengurus OSIS dan satu wali kelas. Hari terakhir ditutup pentas kelas: yel kelompok, pembacaan puisi, dan presentasi hasil tur keliling kampus.</p>
                <p>Materi yang paling sering ditanyakan siswa baru:</p>
                <ul>
                    <li>Alur peminjaman buku dan pemakaian laboratorium.</li>
                    <li>Jadwal setoran hafalan dan nama pendampingnya.</li>
                    <li>Cara memilih ekstrakurikuler sesuai minat.</li>
                </ul>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(95),
            ],
            [
                'title' => 'Ziarah dan Studi Sejarah ke Makam Sunan Ampel',
                'slug' => 'ziarah-sunan-ampel',
                'category_id' => $catKegiatan->id,
                'content' => <<<'HTML'
                <p>Kelas X mengikuti ziarah dan studi sejarah ke Makam Sunan Ampel Surabaya. Kegiatan ini menjadi bagian dari pembelajaran sejarah Islam Nusantara sekaligus penguatan karakter santri.</p>
                <p>Rombongan diterima pengurus makam, dilanjutkan ziarah, doa bersama, dan penjelasan riwayat dakwah Sunan Ampel di kawasan Ampel Denta. Siswa mencatat temuan untuk laporan singkat pelajaran sejarah.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(35),
            ],
            [
                'title' => 'Santunan Anak Yatim dan Dhuafa Bersama Yayasan',
                'slug' => 'santunan-yatim-dhuafa',
                'category_id' => $catKegiatan->id,
                'content' => <<<'HTML'
                <p>Sekolah menyalurkan santunan kepada 120 anak yatim dan dhuafa dari lingkungan sekitar Krian. Dana berasal dari donasi siswa, guru, wali murid, dan sebagian hasil bazar kewirausahaan siswa.</p>
                <p>Acara diisi tausiyah, penyerahan santunan, dan makan bersama. Pengurus yayasan menegaskan penyaluran dibukukan terbuka dan laporannya ditempel di papan informasi sekolah.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(48),
            ],
            [
                'title' => 'Simulasi Tanggap Bencana Bersama BPBD Sidoarjo',
                'slug' => 'simulasi-tanggap-bencana',
                'category_id' => $catKegiatan->id,
                'content' => <<<'HTML'
                <p>Sekolah menggelar simulasi gempa dan kebakaran bersama BPBD Sidoarjo. Seluruh siswa berlatih evakuasi mandiri menuju titik kumpul di lapangan multifungsi saat sirine berbunyi.</p>
                <p>Tim BPBD memandu jalur evakuasi, penggunaan alat pemadam api ringan, dan pertolongan pertama dasar bersama anggota PMR. Dalam evaluasi, waktu evakuasi seluruh warga sekolah tercatat empat menit tiga puluh detik.</p>
                <p>Hasil evaluasi diserahkan ke tim keamanan sekolah untuk memperbaiki jalur keluar di koridor lantai dua.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(50),
            ],
            [
                'title' => 'Pekan Inovasi P5: Pameran Proyek Gaya Hidup Berkelanjutan',
                'slug' => 'pekan-p5-semester-ganjil',
                'category_id' => $catKegiatan->id,
                'content' => <<<'HTML'
                <p>Pekan Inovasi P5 menampilkan proyek kelas X bertema gaya hidup berkelanjutan. Setiap kelas memamerkan satu karya, dari kerajinan bahan daur ulang sampai purwarupa alat penghemat listrik sederhana.</p>
                <p>Panel juri dari guru lintas mapel menilai kesesuaian tema, proses kerja kelompok, dan presentasi. Pengunjung dari kelas XI dan XII memberi suara untuk kategori favorit.</p>
                <p>Tiga proyek terbaik dilanjutkan menjadi bahan Festival Karya Siswa akhir semester.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(30),
            ],
            [
                'title' => 'Pekan Literasi Sekolah: Resensi, Puisi, dan Book Fair',
                'slug' => 'pekan-literasi-sekolah',
                'category_id' => $catKegiatan->id,
                'content' => <<<'HTML'
                <p>Pekan Literasi Sekolah berlangsung lima hari dengan lomba resensi buku, menulis puisi, dan book fair yang melibatkan penerbit lokal. Seluruh kelas mengirim perwakilan.</p>
                <p>Kategori yang dilombakan:</p>
                <ul>
                    <li>Resensi buku fiksi dan non-fiksi.</li>
                    <li>Menulis puisi bertema lingkungan sekolah.</li>
                    <li>Poster ajakan membaca.</li>
                </ul>
                <p>Perpustakaan mencatat kenaikan peminjaman buku selama pekan literasi, sehingga jadwal layanan ditambah sampai pukul 15.30.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(20),
            ],
            [
                'title' => 'Peringatan Maulid Nabi 1448 H dan Lomba Antar Kelas',
                'slug' => 'maulid-nabi-1448h',
                'category_id' => $catKegiatan->id,
                'content' => <<<'HTML'
                <p>Peringatan Maulid Nabi Muhammad SAW 1448 H digelar di Masjid Kampus Al-Iman, diisi tausiyah bersama pengasuh pondok pesantren mitra dan pembacaan selawat oleh tim nasyid sekolah.</p>
                <p>Rangkaian lomba antar kelas berlangsung dua hari: tahfidz juz 30, pidato bahasa Arab, dan kaligrafi. Juaranya diumumkan pada penutupan di lapangan multifungsi.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(42),
            ],
            [
                'title' => 'Aksi Bersih Kampus: Penanaman Tanaman dan Penguatan Bank Sampah',
                'slug' => 'aksi-bersih-lingkungan',
                'category_id' => $catKegiatan->id,
                'content' => <<<'HTML'
                <p>Gerakan sekolah bersih digelar Jumat pertama setiap bulan. Selain membersihkan ruang kelas dan halaman, siswa menanam 200 bibit tanaman yang dibagikan per kelas.</p>
                <p>Sampah anorganik diserahkan ke bank sampah sekolah untuk dipilah. Hasil penjualannya dipakai membeli alat kebersihan dan menambah koleksi buku perpustakaan.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(65),
            ],
            [
                'title' => 'Semarak HUT ke-81 RI: Upacara, Lomba, dan Malam Renungan',
                'slug' => 'hut-ri-81-lomba',
                'category_id' => $catKegiatan->id,
                'content' => <<<'HTML'
                <p>Peringatan HUT ke-81 kemerdekaan RI dibuka dengan upacara di lapangan multifungsi. Petugas upacara berasal dari pengurus OSIS dan anggota Pramuka, diikuti seluruh siswa, guru, dan karyawan.</p>
                <p>Lomba antar kelas berlangsung dua hari: balap karung, tarik tambang, lomba tahfidz cepat, dan turnamen futsal kelas. Malam renungan diisi doa bersama untuk pahlawan bangsa di Masjid Kampus Al-Iman.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(62),
            ],
        ];

        // Posts - Ekstrakurikuler
        $ekstraData = [
            [
                'title' => 'Klub Coding Uji Coba Portal Siswa: Jadwal, Pengumuman, dan Presensi',
                'slug' => 'klub-coding-uji-coba-portal-siswa',
                'category_id' => $catEkstra->id,
                'content' => <<<'HTML'
                <p>Sepuluh anggota Klub Coding menyelesaikan tahap uji coba portal siswa. Aplikasi web ini menampilkan jadwal pelajaran per kelas, pengumuman sekolah, dan rekap presensi.</p>
                <p>Uji coba berjalan dua pekan di tiga kelas. Masukan yang terkumpul dibahas setiap Sabtu: tampilan jadwal perlu diringkas, pencarian pengumuman ditambah, dan notifikasi presensi dikirim ke wali kelas.</p>
                <p>Pengembangan berikutnya menambah fitur pengumpulan tugas dan kalender agenda sekolah. Pendampingan teknis dilakukan kepala tata usaha yang juga mengelola sistem informasi sekolah.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(8),
            ],
            [
                'title' => 'Pramuka Ikut Jambore Ranting Krian',
                'slug' => 'pramuka-jambore-ranting-krian',
                'category_id' => $catEkstra->id,
                'content' => <<<'HTML'
                <p>Kontingen Pramuka SMA XYZ berjumlah 24 anggota mengikuti Jambore Ranting Krian selama tiga hari. Kegiatannya meliputi lomba pionering, lintas alam, penjelajahan, api unggun, dan pentas seni budaya.</p>
                <p>Regu putra meraih juara 3 lomba yel dan regu putri juara harapan 1 lomba pionering. Pembina mencatat evaluasi keterampilan tali-temali untuk latihan rutin berikutnya.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(28),
            ],
            [
                'title' => 'PMR Ikut Pelatihan Pertolongan Pertama dan Bantuan Hidup Dasar',
                'slug' => 'pmr-pelatihan-pertolongan-pertama',
                'category_id' => $catEkstra->id,
                'content' => <<<'HTML'
                <p>Sebanyak 20 anggota PMR mengikuti pelatihan pertolongan pertama bersama tim Puskesmas Krian. Materinya pembidaian, penanganan luka, evakuasi korban, dan bantuan hidup dasar dengan manekin latihan.</p>
                <p>Peserta yang lulus ujian praktik menerima sertifikat dan bertugas sebagai petugas P3K pada kegiatan sekolah seperti Porsis, LDKS, dan upacara besar.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(12),
            ],
            [
                'title' => 'Tim Nasyid Tampil di Festival Nasyid Pelajar Sidoarjo',
                'slug' => 'nasyid-festival-pelajar-sidoarjo',
                'category_id' => $catEkstra->id,
                'content' => <<<'HTML'
                <p>Tim nasyid sekolah tampil di Festival Nasyid Pelajar Sidoarjo yang diikuti 18 sekolah. Mereka membawakan dua lagu wajib dan satu aransemen pilihan dengan diiringi rebana.</p>
                <p>Persiapan berlangsung tiga pekan: latihan vokal, pembagian suara, dan penataan panggung. Pembina menilai penampilan tahun ini lebih rapi dan akan dilanjutkan dengan rekaman dokumentasi untuk arsip sekolah.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(18),
            ],
            [
                'title' => 'Robotika Gelar Workshop Arduino untuk Anggota Baru',
                'slug' => 'robotika-workshop-arduino',
                'category_id' => $catEkstra->id,
                'content' => <<<'HTML'
                <p>Ekstrakurikuler Robotika menggelar workshop dasar Arduino selama dua pertemuan di Lab Komputer 1. Pesertanya 28 siswa anggota baru kelas X dan XI.</p>
                <p>Materi meliputi pengenalan board, sensor, dan pemrograman sederhana. Proyek akhirnya lampu lalu lintas mini dan alarm jarak untuk pintu ruang kelas.</p>
                <p>Peserta terbaik workshop diprioritaskan masuk tim yang disiapkan untuk kompetisi robotika tahun depan.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(22),
            ],
            [
                'title' => 'English Club Gelar English Day: Storytelling, Scrabble, dan Mini Debate',
                'slug' => 'english-day-club',
                'category_id' => $catEkstra->id,
                'content' => <<<'HTML'
                <p>English Club menggelar English Day dengan lomba storytelling, scrabble, dan debat mini berbahasa Inggris. Sebanyak 120 siswa dari semua kelas ikut ambil bagian.</p>
                <p>Juri terdiri atas guru bahasa Inggris dan alumni yang kini berkuliah di jurusan sastra. Pemenang setiap lomba menjadi kandidat tim sekolah untuk lomba bahasa Inggris tingkat kabupaten.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(33),
            ],
            [
                'title' => 'KIR Uji Kulit Kopi sebagai Penjernih Air Sumur',
                'slug' => 'kir-uji-kulit-kopi',
                'category_id' => $catEkstra->id,
                'content' => <<<'HTML'
                <p>Kelompok Ilmiah Remaja menyelesaikan penelitian pemanfaatan kulit kopi sebagai bahan penjernih air sumur. Eksperimen dilakukan di Laboratorium IPA Terpadu selama enam pekan.</p>
                <p>Sampel air diuji kadar kekeruhan dan pH sebelum dan sesudah perlakuan. Hasilnya dibandingkan dengan penjernih komersial, lalu disusun menjadi laporan ilmiah dan dipresentasikan di depan guru pembina.</p>
                <p>Penelitian ini dikirim ke lomba karya tulis ilmiah remaja tingkat kabupaten.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(38),
            ],
            [
                'title' => 'Karantina Tahfidz Akhir Pekan: 40 Siswa Setor Satu Juz',
                'slug' => 'karantina-tahfidz-akhir-pekan',
                'category_id' => $catEkstra->id,
                'content' => <<<'HTML'
                <p>Program karantina tahfidz akhir pekan diikuti 40 siswa kelas X dan XI. Bertempat di Masjid Kampus Al-Iman, kegiatan berjalan dua hari dengan setoran hafalan, tahsin, dan murajaah malam.</p>
                <p>Penutupan diisi tasmi' di depan pembimbing dan orang tua. Empat peserta dengan setoran terbaik mendapat prioritas mewakili sekolah pada MHQ tingkat kabupaten.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(44),
            ],
            [
                'title' => 'Tim Jurnalistik Terbitkan Majalah Sekolah Edisi Perdana',
                'slug' => 'jurnalistik-majalah-edisi-perdana',
                'category_id' => $catEkstra->id,
                'content' => <<<'HTML'
                <p>Tim jurnalistik sekolah menerbitkan majalah Lentera edisi perdana setebal 48 halaman. Isinya laporan kegiatan, profil guru, karya siswa, dan rubrik dokumentasi foto.</p>
                <p>Majalah dicetak 300 eksemplar dan dibagikan ke setiap kelas serta perpustakaan. Halaman digitalnya juga bisa dibaca lewat portal sekolah.</p>
                <p>Edisi kedua mulai digarap dengan fokus liputan kegiatan semester genap.</p>
                HTML,
                'status' => 'published', 'published_at' => now()->subDays(55),
            ],
        ];

        $fasilitasData = [
            ['title' => 'Laboratorium Komputer & Digital Class', 'content' => '40 unit PC Core i7, monitor FHD 24 inci, fiber optik 500 Mbps, dan smart board.', 'slug' => 'laboratorium-komputer-digital-class'],
            ['title' => 'Masjid Kampus Al-Iman', 'content' => 'Masjid dua lantai berkapasitas 800 jamaah dengan ruang kajian dan tahfidz harian.', 'slug' => 'masjid-kampus-al-iman'],
            ['title' => 'Perpustakaan Digital & Media Center', 'content' => 'Koleksi buku fisik, akses e-library nasional, ruang baca ber-AC, dan katalog digital.', 'slug' => 'perpustakaan-digital-media-center'],
            ['title' => 'Laboratorium IPA Terpadu', 'content' => 'Peralatan praktikum Fisika, Kimia, dan Biologi, termasuk mikroskop digital.', 'slug' => 'laboratorium-ipa-terpadu'],
            ['title' => 'Lapangan Olahraga Multifungsi', 'content' => 'Lapangan outdoor untuk Futsal, Basket, Voli, dan Bulutangkis dengan pencahayaan malam.', 'slug' => 'lapangan-olahraga-multifungsi'],
            ['title' => 'Studio Podcast & Multimedia', 'content' => 'Studio kedap suara dengan kamera 4K, lighting, mixer audio, dan podcast microphone.', 'slug' => 'studio-podcast-multimedia'],
            ['title' => 'Ruang Kelas Ber-AC dengan Smart Display', 'content' => 'AC, proyektor interaktif, koneksi wifi, dan lemari buku di setiap ruang kelas.', 'slug' => 'ruang-kelas-ber-ac-smart-display'],
            ['title' => 'Aula Utama & Ruang Serbaguna', 'content' => 'Aula berkapasitas 500 orang untuk pertemuan wali murid, seminar, dan wisuda.', 'slug' => 'aula-utama-ruang-serbaguna'],
            ['title' => 'Kantin Sehat & Koperasi Siswa', 'content' => 'Sepuluh tenant dengan menu tersertifikasi higiene; koperasi menyediakan alat tulis dan perlengkapan sekolah.', 'slug' => 'kantin-sehat-koperasi-siswa'],
            ['title' => 'Ruang UKS & Klinik Layanan Kesehatan', 'content' => 'Ruang periksa dengan petugas piket harian dan kerja sama Puskesmas Krian untuk rujukan.', 'slug' => 'ruang-uks-klinik-sekolah'],
        ];

        $alumniData = [
            ['title' => 'dr. Farhan Al-Ghifari', 'content' => "Pendidikan di SMA XYZ memberi dua fondasi penting: disiplin sains akademik dan hafalan Al-Qur'an yang menenangkan jiwa saat tekanan kuliah kedokteran.", 'slug' => 'dr-farhan-al-ghifari', 'metadata' => ['graduation_year' => 2020, 'current_profession' => 'Dokter Muda / Mahasiswa FK Universitas Airlangga']],
            ['title' => 'Nabila Syakira, S.T.', 'content' => 'Klub Komputer dan Coding di SMA XYZ menjadi pintu awal karir saya di industri IT nasional. Guru-gurunya sangat mendukung bakat siswa!', 'slug' => 'nabila-syakira', 'metadata' => ['graduation_year' => 2021, 'current_profession' => 'Cloud Engineer di Bank Syariah Indonesia Jakarta']],
            ['title' => 'Muhammad Hafiz, Lc.', 'content' => 'Bimbingan Bahasa Arab intensif di SMA XYZ memudahkan saya lolos seleksi beasiswa Al-Azhar Mesir tanpa kendala bahasa.', 'slug' => 'muhammad-hafiz', 'metadata' => ['graduation_year' => 2019, 'current_profession' => 'Alumni Universitas Al-Azhar Kairo / Dai Muda']],
            ['title' => 'Anisa Rahmawati, S.M.', 'content' => 'Pameran Kewirausahaan di sekolah mengajarkan saya berani eksekusi ide bisnis sejak kelas 11. Bangga menjadi alumni SMA XYZ.', 'slug' => 'anisa-rahmawati', 'metadata' => ['graduation_year' => 2022, 'current_profession' => 'Entrepreneur / Founder Startup Fashion Muslim Sidoarjo']],
            ['title' => 'Fauzan Ramadhan, S.Kom.', 'content' => 'Klub Coding dan Olimpiade Informatika di SMA XYZ adalah titik awal saya mengenal pemrograman. Dua hal yang saya bawa dari sekolah: rasa ingin tahu dan disiplin menyelesaikan proyek.', 'slug' => 'fauzan-ramadhan', 'metadata' => ['graduation_year' => 2018, 'current_profession' => 'Software Engineer di Perusahaan Teknologi Singapura']],
            ['title' => 'Ahmad Faisal, S.Si., M.Sc.', 'content' => 'Laboratorium IPA Terpadu dan bimbingan Bu Dewi membuat saya percaya bahwa penelitian bisa dimulai dari pertanyaan sederhana. Kebiasaan itu yang saya bawa sampai sekarang.', 'slug' => 'ahmad-faisal', 'metadata' => ['graduation_year' => 2017, 'current_profession' => 'Peneliti di Badan Riset dan Inovasi Nasional (BRIN)']],
            ['title' => 'Zahra Amelia, S.H.', 'content' => 'Latihan pidato dan debat di SMA XYZ mengajarkan saya berdiri di depan orang dan menyusun argumen yang jujur. Bekal itu saya pakai setiap hari di ruang sidang.', 'slug' => 'zahra-amelia', 'metadata' => ['graduation_year' => 2019, 'current_profession' => 'Advokat Muda pada Kantor Hukum di Surabaya']],
            ['title' => 'Ustadz Rizky Maulana, Lc., M.A.', 'content' => 'Target hafalan yang dulu terasa berat ternyata menjadi modal saya menempuh studi di Mesir. Guru-guru tahfidz di SMA XYZ tidak hanya mengajar menghafal, tetapi juga menjaga adab.', 'slug' => 'rizky-maulana', 'metadata' => ['graduation_year' => 2016, 'current_profession' => 'Dosen dan Penulis Bidang Pendidikan Islam']],
            ['title' => 'Salma Nur Aini, S.Psi.', 'content' => 'Menjadi pendamping MPLS dan pengurus OSIS mengajarkan saya mendengarkan orang lain. Keterampilan itu ternyata yang paling sering saya pakai sekarang.', 'slug' => 'salma-nur-aini', 'metadata' => ['graduation_year' => 2020, 'current_profession' => 'Psikolog di Layanan Konseling Remaja Surabaya']],
            ['title' => 'Putri Andini, S.K.M.', 'content' => 'Pengalaman di PMR dan kegiatan bakti sosial sekolah membuat saya yakin bekerja di bidang kesehatan masyarakat. Ilmu dasarnya justru dimulai dari kepedulian sederhana.', 'slug' => 'putri-andini', 'metadata' => ['graduation_year' => 2021, 'current_profession' => 'Koordinator Promosi Kesehatan Puskesmas Krian']],
        ];

        foreach ([...$beritaData, ...$agendaData, ...$prestasiData, ...$kegiatanData, ...$ekstraData] as $pData) {
            Post::updateOrCreate(['slug' => $pData['slug']], array_merge($pData, ['user_id' => $humas->id]));
        }

        foreach ($fasilitasData as $fData) {
            Post::updateOrCreate(['slug' => $fData['slug']], [
                'user_id' => $admin->id,
                'category_id' => $catFasilitas->id,
                'title' => $fData['title'],
                'slug' => $fData['slug'],
                'content' => $fData['content'],
                'status' => 'published',
                'published_at' => now()->subDays(20),
            ]);
        }

        foreach ($alumniData as $aData) {
            Post::updateOrCreate(['slug' => $aData['slug']], array_merge($aData, [
                'user_id' => $humas->id,
                'category_id' => $catAlumni->id,
                'status' => 'published',
                'published_at' => now()->subDays(30),
            ]));
        }

        // 7. Staff
        $staffData = [
            ['name' => 'Dr. H. Ahmad Dahlan, M.Pd.',       'role' => 'Kepala Sekolah',              'type' => 'gtk',    'email' => 'dahlan@smaxyz.sch.id',  'phone' => '081234567890'],
            ['name' => 'Rina Wulandari, M.Si.',             'role' => 'Waka Kurikulum / Guru Fisika', 'type' => 'guru',   'email' => 'rina@smaxyz.sch.id',    'phone' => '081234567891'],
            ['name' => 'M. Rizky Pratama, S.Pd.',           'role' => 'Waka Kesiswaan / Guru PJOK',  'type' => 'guru',   'email' => 'rizky@smaxyz.sch.id',   'phone' => '081234567892'],
            ['name' => 'Siti Aminah, S.Pd.',                'role' => 'Waka Humas / Guru B. Inggris', 'type' => 'guru',   'email' => 'aminah@smaxyz.sch.id',  'phone' => '081234567893'],
            ['name' => 'Ustadz H. Abdul Samad, Lc., M.H.', 'role' => 'Guru PAI & Koordinator Tahfidz', 'type' => 'guru', 'email' => 'samad@smaxyz.sch.id',   'phone' => '081234567894'],
            ['name' => 'Budi Santoso, S.Kom.',              'role' => 'Kepala Tata Usaha / IT Staff', 'type' => 'tendik', 'email' => 'budi@smaxyz.sch.id',    'phone' => '081234567895'],
            ['name' => 'Dewi Kartika, S.Si.',               'role' => 'Guru Biologi & Pembina OSIS',  'type' => 'guru',   'email' => 'dewi@smaxyz.sch.id',    'phone' => '081234567896'],
            ['name' => 'Eko Prasetyo, S.Pd.',               'role' => 'Guru Matematika',              'type' => 'guru',   'email' => 'eko@smaxyz.sch.id',     'phone' => '081234567897'],
            ['name' => 'Hj. Nurul Hidayah, S.Ag.',          'role' => 'Guru Bahasa Arab',             'type' => 'guru',   'email' => 'nurul@smaxyz.sch.id',   'phone' => '081234567898'],
            ['name' => 'Bambang Utomo, A.Md.',              'role' => 'Staf Perpustakaan',            'type' => 'tendik', 'email' => 'bambang@smaxyz.sch.id', 'phone' => '081234567899'],
        ];
        foreach ($staffData as $s) {
            Staff::updateOrCreate(['email' => $s['email']], $s);
        }

        // 8. Galleries (disimpan sebagai posts kategori galeri)
        $catGaleri = Category::firstOrCreate(['slug' => 'galeri'], ['name' => 'Galeri', 'show_on_home' => false, 'preview_mode' => 'media']);
        $galleriesData = [
            'Upacara Peringatan Hari Kemerdekaan RI di SMA XYZ',
            'Kunjungan Edukasi Industri Komputer Siswa SMA XYZ',
            'Wisuda Tahfidz Al-Qur\'an Angkatan ke-15',
            'Juara Futsal Sidoarjo High School Cup 2026',
            'Bakti Sosial & Pembagian Takjil Ramadhan',
            'Praktikum Biologi Ekosistem di Lab IPA Terpadu',
            'Pentas Seni Islam & Bazar Kewirausahaan',
            'Studi Lapangan Pelatihan Kepemimpinan OSIS',
            'Pameran Proyek P5 Gaya Hidup Berkelanjutan',
        ];
        foreach ($galleriesData as $title) {
            Post::updateOrCreate(['slug' => Str::slug($title)], [
                'user_id' => $humas->id,
                'category_id' => $catGaleri->id,
                'title' => $title,
                'content' => '',
                'media_type' => 'image',
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        Post::updateOrCreate(['slug' => 'dokumentasi-video-pesantren-kilat'], [
            'user_id' => $humas->id,
            'category_id' => $catGaleri->id,
            'title' => 'Dokumentasi Video: Dokumentasi Video Pesantren Kilat Ramadhan',
            'content' => 'Video dokumentasi kegiatan Pesantren Kilat Ramadhan yang diikuti seluruh siswa kelas X dan XI.',
            'media_type' => 'video',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
