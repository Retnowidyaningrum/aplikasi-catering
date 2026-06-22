# MANUAL BOOK - Aplikasi Catering Online

## 📋 Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Cara Memesan](#cara-memesan)
3. [Melacak Pesanan](#melacak-pesanan)
4. [Konfirmasi Pembayaran](#konfirmasi-pembayaran)
5. [Beri Ulasan](#beri-ulasan)
6. [FAQ](#faq)

---

## 1. Pendahuluan {#pendahuluan}

**Cateringku** adalah aplikasi pemesanan catering online yang memungkinkan pelanggan untuk:

- Melihat menu catering yang tersedia
- Memesan makanan secara online
- Melacak status pesanan
- Mengkonfirmasi pembayaran
- Memberikan ulasan setelah pesanan selesai

---

## 2. Cara Memesan {#cara-memesan}

### Langkah 1: Pilih Menu

1. Buka halaman utama di `public/index.php`
2. Pilih kategori menu yang diinginkan
3. Klik tombol **"Pesan"** pada menu yang ingin dipesan

### Langkah 2: Isi Form Pemesanan

1. **Data Pelanggan**
   - Nama Lengkap
   - Nomor Telepon
   - Email (opsional)
   - Alamat Pengiriman

2. **Pilih Menu & Jumlah**
   - Menu yang dipilih akan otomatis terisi
   - Tambah/kurangi jumlah sesuai kebutuhan
   - Tambahkan menu lain jika diperlukan

3. **Metode Pengiriman**
   - **Ambil di Tempat (Pickup)**: Tidak dikenakan biaya pengiriman
   - **Antar ke Rumah**: Gratis jika total pesanan ≥ Rp 500.000, otherwise Rp 15.000

4. **Metode Pembayaran**
   - **COD (Bayar di Tempat)**: Bayar saat pesanan diterima
   - **Transfer Bank**: Bayar via transfer ke rekening BCA

5. **Jadwal**
   - Tanggal Pemesanan
   - Tanggal Pengiriman/Ambil

6. **Catatan**
   - Tambahkan catatan khusus untuk pesanan (opsional)

### Langkah 3: Simpan Pesanan

1. Klik tombol **"Pesan Sekarang"**
2. Simpan **Nomor Invoice** yang ditampilkan (contoh: `INV/20260427/123`)
3. Invoice akan digunakan untuk melacak pesanan

---

## 3. Melacak Pesanan {#melacak-pesanan}

### Cara Melacak:

1. Buka halaman **"Lacak"** di `public/tracking.php`
2. Masukkan **Nomor Invoice** yang erhalten saat pemesanan
3. Klik tombol **"Cek"**

### Status Pesanan:

| Status        | Keterangan                        |
| ------------- | --------------------------------- |
| ⏳ Pending    | Pesanan baru, menunggu konfirmasi |
| ⚙️ Diproses   | Pesanan sedang disiapkan          |
| ✅ Selesai    | Pesanan sudah selesai             |
| ❌ Dibatalkan | Pesanan dibatalkan                |

### Informasi yang Ditampilkan:

- Detail pesanan (menu, jumlah, harga)
- Total pembayaran
- Metode pengiriman & pembayaran
- Status pesanan real-time

---

## 4. Konfirmasi Pembayaran {#konfirmasi-pembayaran}

### Jika Memilih Transfer Bank:

1. Setelah memesan, catat informasi pembayaran:
   - **Bank**: BCA
   - **Nomor Rekening**: 1234 5678 9012 3456
   - **a.n.**: Cateringku

2. Lakukan transfer sesuai total pesanan

3. Buka halaman **Konfirmasi Pembayaran**:
   - Via link di halaman sukses pemesanan
   - Atau akses langsung `public/confirm_payment.php`

4. Masukkan **Nomor Invoice** dan klik **Cek**

5. Upload **Bukti Transfer** (format: JPG, PNG, PDF)

6. Klik **"Kirim"**

### Jika Memilih COD:

- Tidak perlu konfirmasi pembayaran
- Bayar langsung saat pesanan diterima/diambil

---

## 5. Beri Ulasan {#beri-ulasan}

Setelah pesanan **selesai**, Anda dapat memberikan ulasan:

1. Buka halaman review via link di tracking atau email
2. Masukkan **Nomor Invoice**
3. Beri **Rating** (1-5 bintang)
4. Tulis **Komentar/Ulasan** (opsional)
5. Klik **"Kirim Ulasan"**

### Skala Rating:

- ⭐⭐⭐⭐⭐ = Sangat Puas
- ⭐⭐⭐⭐ = Puas
- ⭐⭐⭐ = Cukup
- ⭐⭐ = Kurang Puas
- ⭐ = Tidak Puas

---

## 6. FAQ {#faq}

### Q: Bagaimana jika lupa nomor invoice?

**A:** Hubungi admin melalui telepon atau WhatsApp yang tertera di website dengan menyertakan nama dan tanggal pemesanan.

### Q: Apakah bisa mengubah pesanan setelah submit?

**A:** Hubungi admin sebelum pesanan berstatus "Diproses".

### Q: Berapa lama waktu pengantaran?

**A:** Waktu pengantaran tergantung pada jadwal yang dipilih saat pemesanan. Typically 1-2 jam untuk area sekitar.

### Q: Bagaimana jika makanan tidak sesuai pesanan?

**A:** Segera hubungi admin dengan menyertakan bukti foto makanan yang tidak sesuai.

### Q: Apakah bisa membatalkan pesanan?

**A:** Bisa, selama status pesanan masih "Pending". Hubungi admin untuk proses pembatalan.

---

## 📞 Hubungi Kami

Jika ada pertanyaan lebih lanjut:

- **Telepon**: [Nomor Telepon]
- **WhatsApp**: [Nomor WhatsApp]
- **Email**: [Email Address]
- **Alamat**: [Alamat Catering]

---

_Manual Book v1.0 - Cateringku_
_Terakhir diperbarui: April 2026_
