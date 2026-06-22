# AGENTS.md — Aturan Baku untuk Semua AI Model

> File ini adalah panduan wajib bagi **semua AI model** (termasuk LLM, code assistant, agent AI) dalam proses pengembangan dan pemeliharaan aplikasi **Cateringku**.

## Daftar Isi

1. [Workflow Pengembangan](#1-workflow-pengembangan)
2. [Aturan Kode (Coding Standards)](#2-aturan-kode-coding-standards)
3. [Keamanan (Security)](#3-keamanan-security)
4. [Database](#4-database)
5. [Testing](#5-testing)
6. [Git & Commit](#6-git--commit)
7. [CI/CD Pipeline](#7-cicd-pipeline)
8. [Larangan (Do Not)](#8-larangan-do-not)
9. [Referensi File Penting](#9-referensi-file-penting)

---

## 1. Workflow Pengembangan

Setiap perubahan WAJIB mengikuti alur berikut:

```
[ANALISIS]
  ├── Pahami kode yang ada sebelum mengubah
  └── Baca file terkait, pahami konteks
       │
       ▼
[IMPLEMENTASI]
  ├── Lakukan perubahan di localhost (XAMPP)
  └── Ikuti aturan kode di bawah
       │
       ▼
[TEST LOKAL]
  ├── Jalankan test.ps1 (syntax check semua file PHP)
  ├── Buka di browser: http://localhost/catering/
  ├── Uji manual fitur yang diubah
  ├── Pastikan tidak ada error/warning PHP
  └── Jika gagal → kembali ke [IMPLEMENTASI]
       │
       ▼
[COMMIT & PUSH]
  ├── Staging file yang relevan saja
  ├── Tulis commit message sesuai format
  ├── git commit -m "<type>: <deskripsi>"
  └── git push origin <branch>
       │
       ▼
[CI/CD]
  ├── GitHub Actions otomatis menjalankan pipeline
  ├── Syntax check semua file PHP
  ├── Integration test (jika ada)
  └── Jika gagal → analisis log CI → kembali ke [IMPLEMENTASI]
       │
       ▼
[SELESAI]
  └── Pipeline hijau → tugas selesai
```

**PENTING:** Jangan pernah skip testing lokal. Jangan push code yang belum di-test.

---

## 2. Aturan Kode (Coding Standards)

### 2.1 PHP

| Aturan | Standar |
|--------|---------|
| Tag PHP | `<?php` (jangan pernah gunakan short tag `<?`) |
| Penamaan file | `snake_case.php` |
| Penamaan fungsi | `camelCase()` |
| Penamaan variabel | `camelCase` |
| Penamaan konstanta | `UPPER_SNAKE_CASE` |
| Indentasi | 4 spasi (bukan tab) |
| String | Gunakan `'kutip tunggal'` kecuali ada interpolasi |
| Query SQL | Prepared statement via PDO selalu |

### 2.2 HTML

| Aturan | Standar |
|--------|---------|
| Bootstrap | 5.3.x (via CDN) — gunakan class Bootstrap |
| CSS kustom | Hanya jika Bootstrap tidak mencukupi |
| ID unik | Setiap elemen harus punya ID unik |
| Form | Setiap input harus punya label |

### 2.3 JavaScript

| Aturan | Standar |
|--------|---------|
| jQuery | 3.6.x (via CDN) — hanya jika perlu |
| Select2 | 4.1.x (via CDN) — untuk searchable dropdown |
| Event handler | Gunakan `addEventListener()` atau `$(...).on()` |
| DOM manipulation | Minimal, prefer server-side rendering |

### 2.4 Struktur Direktori

```
catering/
├── config/           # Konfigurasi (database.php)
├── inc/              # Include files (sidebar.php)
├── public/           # Halaman publik (customer-facing)
├── uploads/          # File upload (menu images, payment proofs)
│   ├── menus/
│   └── payments/
├── *.php             # Halaman admin di root
├── AGENTS.md         # Aturan AI (file ini)
├── AGENT.py          # Standalone Python script (jangan diubah)
└── MANUAL_BOOK.md    # Manual pengguna
```

---

## 3. Keamanan (Security)

### 3.1 Wajib Dilakukan

- [ ] **Prepared Statements** — Setiap query SQL WAJIB menggunakan PDO prepared statement. Jangan pernah melakukan string interpolation pada query.
- [ ] **Password Hashing** — Gunakan `password_hash()` dan `password_verify()`. Jangan simpan password plain-text.
- [ ] **Session Security** — Setiap halaman admin harus cek `$_SESSION['login']`. Gunakan `session_regenerate_id()` setelah login.
- [ ] **Validasi Input** — Validasi semua input (POST/GET) sebelum diproses. Filter tipe data dengan `filter_var()`.
- [ ] **CSRF Protection** — Form WAJIB memiliki token CSRF (tersimpan di session).
- [ ] **Upload File** — Validasi ekstensi file (hanya JPG, PNG, WEBP, GIF). Batasi ukuran file. Jangan percaya MIME type dari client.

### 3.2 Tidak Boleh Dilakukan

- ❌ Jangan pernah menggunakan `mysql_*` functions
- ❌ Jangan pernah menggunakan `eval()`, `exec()`, `system()`, `shell_exec()`, `popen()`
- ❌ Jangan pernah hardcode password/kredensial di kode
- ❌ Jangan pernah menampilkan error PHP ke user (`display_errors = Off` di production)
- ❌ Jangan pernah mempercayai input user tanpa validasi
- ❌ Jangan pernah menyimpan password tanpa hashing

---

## 4. Database

### 4.1 Koneksi

- File: `config/database.php`
- Driver: PDO MySQL
- Error mode: `ERRMODE_EXCEPTION`
- Charset: `utf8mb4`

### 4.2 Tabel

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Admin login (username, password) |
| `customers` | Data pelanggan (name, phone, email, address) |
| `menus` | Menu catering (name, category, price, image) |
| `orders` | Pesanan (customer, dates, totals, status, payment) |
| `order_items` | Item dalam pesanan (menu, quantity, subtotal) |
| `reviews` | Ulasan pelanggan (rating, comment) |

### 4.3 Aturan Migrasi

- Setiap perubahan tabel WAJIB ditambahkan sebagai komentar di file terkait atau migration file
- Jangan hapus kolom tanpa backup data
- Gunakan tipe data yang tepat (DECIMAL untuk harga, DATE untuk tanggal, dll)

---

## 5. Testing

### 5.1 Test Lokal (Wajib)

Sebelum commit, jalankan:

```powershell
.\test.ps1
```

Script ini akan melakukan:
- Syntax check semua file `.php` dengan `php -l`
- Verifikasi konfigurasi database

### 5.2 Test Manual

Setiap fitur yang diubah WAJIB diuji manual:
1. Buka http://localhost/catering/
2. Login dengan kredensial admin
3. Uji fitur yang diubah (CRUD, form, navigasi)
4. Cek console browser (F12) untuk JavaScript error
5. Cek error log PHP (`xampp\php\logs\php_error_log`)

### 5.3 CI Testing (Otomatis)

Setiap push ke GitHub akan memicu:
- PHP syntax check (`php -l`) pada semua file `.php`
- Verifikasi struktur kode

---

## 6. Git & Commit

### 6.1 Format Commit Message

```
<type>: <deskripsi singkat>

[opsional: penjelasan lebih detail]
```

**Type:**
| Type | Kapan Digunakan |
|------|----------------|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `refactor` | Perubahan kode tanpa mengubah fungsionalitas |
| `style` | Perubahan format (indentasi, spacing, dll) |
| `docs` | Perubahan dokumentasi |
| `chore` | Maintenance, setup, tooling |
| `test` | Penambahan/perbaikan test |
| `perf` | Optimasi performa |
| `security` | Perbaikan keamanan |

**Contoh:**
```
feat: add delivery fee calculator to order form

fix: prevent SQL injection on customer search

chore: init git repository and setup CI pipeline

security: hash passwords with password_hash()
```

### 6.2 Aturan Commit

- [ ] Satu commit = satu perubahan logis
- [ ] Jangan commit file yang tidak relevan
- [ ] Jangan commit `.env`, kredensial, atau file sensitif
- [ ] Tulis `body` commit jika perubahan perlu penjelasan
- [ ] Gunakan bahasa Indonesia atau Inggris (konsisten dalam satu commit)
- [ ] Referensi issue/PR jika ada: `fixes #42`

### 6.3 Branch Strategy

| Branch | Tujuan |
|--------|--------|
| `main` | Branch utama, production-ready |
| `dev` | Pengembangan fitur (jika kolaborasi) |
| `fix/<nama>` | Perbaikan bug |
| `feat/<nama>` | Fitur baru |

---

## 7. CI/CD Pipeline

### 7.1 GitHub Actions Workflow

File: `.github/workflows/ci.yml`

**Trigger:** Setiap push ke `main` dan pull request

**Jobs:**
1. `php-lint` — Syntax check semua file PHP
2. `security-check` — Scan kredensial hardcoded (jika feasible)

### 7.2 Jika CI Gagal

1. Buka tab Actions di GitHub
2. Klik workflow yang gagal
3. Baca log error dengan seksama
4. Identifikasi file dan baris yang error
5. Perbaiki di lokal
6. Test lokal → commit → push lagi
7. Ulangi sampai pipeline hijau

---

## 8. Larangan (Do Not)

AI model DILARANG melakukan hal berikut:

| # | Larangan | Alasan |
|---|----------|--------|
| 1 | Mengubah AGENT.py | File Python standalone, tidak terkait PHP app |
| 2 | Menghapus file tanpa konfirmasi | Bisa merusak struktur proyek |
| 3 | Mengubah struktur database tanpa dokumentasi | Migrasi harus tercatat |
| 4 | Menambahkan dependensi eksternal (Composer, npm) | Proyek menggunakan CDN |
| 5 | Mengganti framework CSS/JS | Bootstrap 5 sudah ditetapkan |
| 6 | Menambahkan file duplikat | Cek apakah file sudah ada |
| 7 | Mengabaikan error yang ada | Jangan tinggalkan error untuk nanti |
| 8 | Menulis kode tanpa memahami konteks | Baca file terkait dulu |
| 9 | Me-skip testing lokal | Testing adalah kewajiban |
| 10 | Push langsung ke `main` tanpa review | Gunakan branch jika kolaborasi |

---

## 9. Referensi File Penting

| File | Path | Deskripsi |
|------|------|-----------|
| DB Config | `config/database.php` | Koneksi database + konstanta sistem |
| Sidebar | `inc/sidebar.php` | Navigasi admin sidebar |
| Login | `index.php` | Halaman login admin |
| Dashboard | `dashboard.php` | Dashboard admin dengan statistik |
| Menu CRUD | `menu.php` | Kelola menu + upload gambar |
| Customer CRUD | `customer.php` | Kelola pelanggan |
| Order List | `order.php` | Daftar pesanan + update status |
| Create Order | `order_create.php` | Buat pesanan baru (admin) |
| Reports | `report.php` | Laporan harian/mingguan/bulanan |
| Public Menu | `public/index.php` | Katalog menu publik |
| Public Order | `public/order.php` | Form pemesanan publik |
| Tracking | `public/tracking.php` | Lacak pesanan via invoice |
| Payment Conf | `public/confirm_payment.php` | Upload bukti bayar |
| Reviews | `public/review.php` | Rating & ulasan |
| Rules | `AGENTS.md` | File ini — aturan AI model |
| Manual | `MANUAL_BOOK.md` | Panduan pengguna |

---

> **Prinsip Utama:** "Simple, aman, dan teruji lebih baik daripada canggih tapi rapuh."
>
> Referensi implementasi: Lihat `AGENT.py` untuk contoh pendekatan production-safe.
