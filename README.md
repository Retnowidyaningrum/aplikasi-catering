# NAMA  : RETNO WIDYA NINGRUM
# NIM   : 101230005
# KELAS : TF23A

# Aplikasi Catering (Cateringku)

Sistem Pemesanan Catering Online

---

##  Daftar Isi

- [Tentang Proyek](#tentang-proyek)
- [Fitur Unggulan](#fitur-unggulan)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Struktur Direktori](#struktur-direktori)
- [Panduan Penggunaan](#panduan-penggunaan)
- [Fungsi Helper](#fungsi-helper)
- [CI/CD Pipeline](#cicd-pipeline)
- [Keamanan](#keamanan)
- [Kontribusi](#kontribusi)
- [Lisensi](#lisensi)
- [Kontak](#kontak)

---

##  Tentang Proyek

**Aplikasi Catering (Cateringku)** adalah aplikasi web pemesanan catering online yang menghubungkan pelanggan dengan penyedia jasa katering. Aplikasi ini memudahkan pemesanan makanan, pelacakan pesanan, konfirmasi pembayaran, hingga sistem rating dan ulasan.

---

##  Fitur Unggulan

###  Untuk Pelanggan (Public Area)

| Fitur | Deskripsi |
|-------|-----------|
| **Lihat Menu** | Menampilkan menu dengan gambar, nama, kategori, harga, dan rating dari pelanggan lain |
| **Filter Kategori** | Menyaring menu berdasarkan kategori (Paket Hemat, Prasmanan, Snack, Box) |
| **Pemesanan Online** | Form pemesanan lengkap dengan pilihan menu, jumlah, dan catatan khusus |
| **Metode Pengiriman** | Antar ke Rumah (ongkir Rp 15.000) atau Ambil di Tempat (gratis) |
| **Gratis Ongkir** | Otomatis gratis jika total pesanan ≥ Rp 500.000 |
| **Metode Pembayaran** | COD (Bayar di Tempat) atau Transfer Bank (BCA) |
| **Nomor Invoice** | Mendapatkan invoice otomatis setelah pemesanan berhasil |
| **Lacak Pesanan** | Cek status pesanan real-time dengan nomor invoice |
| **Progress Status** | Melihat progress pesanan (Pending → Diproses → Selesai) |
| **Konfirmasi Pembayaran** | Upload bukti transfer untuk pembayaran via bank |
| **Rating & Ulasan** | Memberi bintang (1-5) dan komentar untuk pesanan yang sudah selesai |

###  Untuk Admin (Admin Area)

| Fitur | Deskripsi |
|-------|-----------|
| **Login Admin** | Sistem autentikasi dengan proteksi CSRF |
| **Dashboard** | Ringkasan total pendapatan dan jumlah pesanan |
| **Manajemen Menu** | Tambah, edit, hapus menu dengan upload gambar (JPG, PNG, WEBP, GIF, max 2MB) |
| **Manajemen Pesanan** | Lihat semua pesanan, detail pesanan, update status (Pending/Diproses/Selesai/Dibatalkan) |
| **Hapus Pesanan** | Menghapus pesanan beserta itemnya |
| **Input Pesanan Manual** | Membuat pesanan untuk pelanggan yang memesan via telepon/WhatsApp |
| **Manajemen Pelanggan** | Menambah dan melihat data pelanggan |
| **Laporan** | Filter harian, mingguan, bulanan, atau semua periode |
| **Cetak Laporan** | Mencetak laporan pendapatan dan daftar pesanan |

---

##  Teknologi yang Digunakan

| Komponen | Teknologi | Keterangan |
|----------|-----------|------------|
| **Backend** | PHP 8.1+ | Bahasa pemrograman utama |
| **Database** | MySQL 5.7+ | Penyimpanan data |
| **Koneksi DB** | PDO | Koneksi database aman |
| **Frontend** | HTML5, CSS3, JavaScript | Struktur dan interaktivitas |
| **CSS Framework** | Bootstrap 5.3 | Desain responsif |
| **Icon Library** | Font Awesome 6.4 | Koleksi icon |
| **Dropdown Library** | Select2 | Dropdown dengan pencarian |
| **Version Control** | Git | Manajemen kode |
| **CI/CD** | GitHub Actions | Otomatisasi testing |

---

##  Persyaratan Sistem

### Minimal Requirements

**Server:**
- Web Server (Apache/Nginx)
- PHP 8.1 atau lebih tinggi
- MySQL 5.7 atau MariaDB 10.2+

**Extension PHP yang Dibutuhkan:**
- PDO & PDO_MySQL - Koneksi database
- GD Library - Manipulasi gambar
- Fileinfo - Validasi file
- JSON - Format data
- Session - Manajemen session

**Rekomendasi:**
- RAM: 2GB atau lebih
- Storage: 100MB untuk aplikasi
- Browser: Chrome, Firefox, Edge, Safari terbaru

---

##  Instalasi

### Langkah 1: Clone Repository
Clone repository dari GitHub ke komputer Anda.

### Langkah 2: Setup Database
Buat database baru dengan nama yang diinginkan, lalu import file database yang disediakan.

### Langkah 3: Konfigurasi Database
Buat file konfigurasi database dan isi dengan pengaturan koneksi (host, nama database, username, password).

### Langkah 4: Buat Folder Upload
Buat folder untuk menyimpan gambar menu dan bukti pembayaran, lalu beri izin akses yang sesuai.

### Langkah 5: Buat User Admin
Tambahkan user admin ke dalam database dengan username dan password yang diinginkan.

### Langkah 6: Akses Aplikasi
- **Admin**: Buka folder admin di browser, login dengan username dan password admin
- **Customer**: Buka folder public di browser untuk mulai melihat menu

---

##  Struktur Direktori

```
aplikasi-catering/
│
├── admin/                          # Admin Area (Backend)
│   ├── index.php                  # Login Admin
│   ├── dashboard.php              # Dashboard
│   ├── menu.php                   # Kelola Menu
│   ├── order.php                  # Daftar Pesanan
│   ├── order_create.php           # Input Pesanan Manual
│   ├── report.php                 # Laporan
│   ├── customer.php               # Kelola Pelanggan
│   └── inc/
│       └── sidebar.php            # Sidebar Admin
│
├── public/                         # Public Area (Frontend - Pelanggan)
│   ├── index.php                  # Halaman Beranda
│   ├── order.php                  # Form Pemesanan
│   ├── tracking.php               # Lacak Pesanan
│   ├── confirm_payment.php        # Konfirmasi Pembayaran
│   └── review.php                 # Rating & Ulasan
│
├── config/
│   └── database.php               # Konfigurasi Database
│
├── inc/
│   └── helpers.php                # Fungsi Helper Global
│
├── uploads/                        # File Upload
│   ├── menus/                     # Gambar Menu
│   └── payments/                  # Bukti Pembayaran
│
├── .github/
│   └── workflows/
│       └── ci.yml                 # CI/CD Pipeline
│
├── README.md                       # Dokumentasi
└── LICENSE                         # Lisensi
```

---

##  Panduan Penggunaan

###  Untuk Pelanggan

| Langkah | Aksi |
|---------|------|
| **1. Lihat Menu** | Buka halaman utama untuk melihat semua menu dengan gambar, harga, dan rating |
| **2. Filter Menu** | Gunakan dropdown kategori untuk menyaring menu yang diinginkan |
| **3. Pemesanan** | Klik tombol "Pesan" pada menu, lalu isi data diri dan pilih menu & jumlah |
| **4. Metode Pengiriman** | Pilih Antar ke Rumah (dengan ongkir) atau Ambil di Tempat (gratis) |
| **5. Metode Pembayaran** | Pilih COD (bayar di tempat) atau Transfer Bank (BCA) |
| **6. Dapatkan Invoice** | Simpan nomor invoice yang muncul setelah pemesanan berhasil |
| **7. Lacak Pesanan** | Buka halaman lacak, masukkan nomor invoice untuk cek status |
| **8. Konfirmasi Bayar** | Jika transfer, buka halaman konfirmasi dan upload bukti transfer |
| **9. Beri Rating** | Setelah pesanan selesai, beri rating bintang dan tulis ulasan |

###  Untuk Admin

| Langkah | Aksi |
|---------|------|
| **1. Login** | Buka halaman login admin, masukkan username dan password |
| **2. Dashboard** | Lihat ringkasan total pendapatan dan jumlah pesanan |
| **3. Kelola Menu** | Tambah menu baru, edit menu yang ada, atau hapus menu beserta gambarnya |
| **4. Kelola Pesanan** | Lihat semua pesanan, klik untuk melihat detail, update status pesanan |
| **5. Input Manual** | Buat pesanan untuk pelanggan yang memesan via telepon/WhatsApp |
| **6. Laporan** | Pilih filter laporan (harian/mingguan/bulanan), lalu cetak laporan |

---

##  Fungsi Helper

| Fungsi | Deskripsi |
|--------|-----------|
| `rp()` | Memformat angka menjadi format Rupiah (contoh: 25000 menjadi Rp 25.000) |
| `csrfField()` | Menghasilkan input hidden untuk token CSRF di setiap form |
| `verifyCsrfToken()` | Memverifikasi token CSRF untuk keamanan form |
| `getStatusBadge()` | Menghasilkan badge warna untuk status pesanan di halaman admin |
| `getStatusBadgeUser()` | Menghasilkan badge warna untuk status pesanan di halaman customer |
| `getPaymentBadge()` | Menghasilkan badge untuk metode pembayaran di halaman admin |
| `getPaymentBadgeUser()` | Menghasilkan badge untuk metode pembayaran di halaman customer |
| `getProgressStep()` | Menentukan progress step tracking berdasarkan status pesanan |

---

##  CI/CD Pipeline

Proyek ini menggunakan **GitHub Actions** untuk Continuous Integration.

### Jobs

| Job | Fungsi | Trigger |
|-----|--------|---------|
| **PHP Syntax Check** | Memeriksa error sintaks di semua file PHP | Push ke main/dev, Pull Request ke main |
| **Security Scan** | Memindai hardcoded credentials, eval(), fungsi berbahaya | Push ke main/dev, Pull Request ke main |

### Security Scan Detail
- Memeriksa penggunaan fungsi `mysql_*` yang sudah deprecated
- Memeriksa penggunaan `eval()` yang berbahaya
- Memeriksa penggunaan fungsi berbahaya seperti `shell_exec`, `exec`, `system`, `popen`
- Memeriksa adanya hardcoded credentials

---

##  Keamanan

| Aspek | Implementasi | Keterangan |
|-------|--------------|------------|
| **SQL Injection** | Prepared Statements (PDO) | Semua query menggunakan prepared statements |
| **XSS** | htmlspecialchars() | Semua output di-escape untuk mencegah XSS |
| **CSRF** | Token di setiap form | Setiap form memiliki token CSRF untuk keamanan |
| **Password** | password_hash() bcrypt | Password disimpan dengan hash yang aman |
| **Session** | HTTPOnly, Secure flags | Session dikonfigurasi dengan aman |
| **Upload** | Validasi format & ukuran | Hanya file gambar dengan ukuran maksimal 2MB yang diizinkan |
| **Error Handling** | Tidak menampilkan detail sensitif | Error tidak menampilkan informasi database |

---

##  Kontribusi

### Cara Berkontribusi

1. Fork repository ke akun GitHub Anda
2. Clone fork ke komputer lokal
3. Buat branch baru untuk fitur atau perbaikan
4. Lakukan perubahan dan commit dengan pesan yang jelas
5. Push ke branch di fork Anda
6. Buat Pull Request ke repository utama

### Pedoman Commit

| Prefix | Keterangan |
|--------|------------|
| `feat:` | Fitur baru |
| `fix:` | Perbaikan bug |
| `docs:` | Dokumentasi |
| `style:` | Formatting |
| `refactor:` | Refaktor kode |
| `test:` | Testing |
| `chore:` | Maintenance |

---

##  Lisensi

**MIT License** - Bebas digunakan, dimodifikasi, dan didistribusikan.

---

##  Kontak

- **GitHub**: [https://github.com/Retnowidyaningrum/aplikasi-catering](https://github.com/Retnowidyaningrum/aplikasi-catering)
- **Issues**: [https://github.com/Retnowidyaningrum/aplikasi-catering/issues](https://github.com/Retnowidyaningrum/aplikasi-catering/issues)

---

##  Changelog

### v1.0.0 (2024)
- ✅ Rilis perdana
- ✅ Fitur pemesanan online
- ✅ Tracking pesanan dengan invoice
- ✅ Konfirmasi pembayaran transfer
- ✅ Rating & ulasan pelanggan
- ✅ Manajemen admin (menu, pesanan, laporan)
- ✅ CI/CD pipeline (PHP Syntax Check + Security Scan)
- ✅ Dokumentasi lengkap

---

**Dibuat dengan ❤️ untuk usaha Catering Indonesia**
