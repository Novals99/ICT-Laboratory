# Rangkuman Perbaikan (Changelog)

Dokumen ini merangkum semua perubahan, perbaikan bug, dan penyesuaian fitur yang telah dikerjakan sesuai dengan permintaan revisi penguji dan pengguna.

---

## 1. Perbaikan & Redesain Modal Create Transfer Request
- **Pemilihan Kategori Aset (Choose Category):** Mengubah input kategori dari dropdown per baris menjadi tombol global di level modal (**Electronic**, **PC Component**, **PC**, **Non-Electronic**). Tombol ini dapat diaktifkan bersamaan (multi-select/toggled) untuk menyaring (filter) daftar aset yang muncul secara dinamis.
- **Filter PC (Choose PC):** Menambahkan baris dropdown "Choose PC". Jika PC tertentu dipilih, daftar nama aset yang tersedia di item baris akan difilter secara otomatis sehingga hanya memunculkan komponen-komponen yang saat ini terpasang pada PC tersebut.
- **Kondisi Hak Akses Laboratorium (Choose Laboratory):** 
  - Jika pengguna login sebagai **Staff** (hanya memiliki 1 lab), pilihan laboratorium asal dikunci berupa teks biasa (read-only) dengan input tersembunyi (*hidden input*).
  - Jika pengguna login sebagai **PIC/SPV**, menu dropdown pilihan laboratorium asal tetap aktif.
- **Penghapusan Opsi "Lost/Hilang":** Menghilangkan opsi kondisi "Lost" / "Hilang" dari dropdown kondisi retur, karena barang yang hilang tidak memiliki fisik untuk ditransfer atau diretur.

---

## 2. Redesain Modal Create Return Request & Halaman Retur
- **Tombol Kategori Global:** Mengimplementasikan fungsionalitas tombol kategori global (multi-select) yang serupa dengan modul mutasi untuk mempermudah pemfilteran aset.
- **Penyaringan Berdasarkan PC:** Menambahkan dropdown "Choose PC" untuk membatasi pilihan aset hanya pada komponen yang terpasang di PC terpilih.
- **Akses Lab Terkunci untuk Staff:** Menyesuaikan field pilihan lab asal agar tampil read-only bagi user dengan role Staff.
- **Pembersihan Opsi Kondisi:** Menghapus opsi kondisi "Lost" (di menu retur bahasa Inggris) dan "Hilang" (di menu retur bahasa Indonesia) dari dropdown kondisi barang yang diretur:
  - Form modal retur utama (`return-requests/index.blade.php`)
  - Halaman buat retur (`return-requests/create.blade.php`)
  - Modal retur cepat / quick return (`pages/laboratory/show.blade.php`)

---

## 3. Penyempurnaan Informasi Aset & Inventaris (Lab & Gudang)
- **Format Gabungan Nama & Spesifikasi:** Menyatukan format tampilan nama barang dengan spesifikasinya menjadi `Nama - Spesifikasi` (contoh: `Intel Core i7 - i7 3440`) di beberapa halaman berikut:
  - Tabel utama daftar Inventory ([Inventory List](file:///c:/InventoryFinal2/ICT-Laboratory/resources/views/pages/laboratory/show.blade.php))
  - Dropdown pilihan aset pada modal tambah aset
  - Dropdown formulir pembuatan laboratorium langkah kedua (Step 2 Wizard)
- **Istilah Kode Inventaris:** Mengganti semua label dan fungsionalitas "Serial Number" menjadi **"Kode Inventaris"** untuk menyelaraskan dengan struktur data terbaru dari gudang.
- **Modal Add Asset (PC Component):** Menghilangkan field pilihan *Kode Inventaris dari SPV* khusus untuk aset berkategori PC Component.

---

## 4. Perbaikan Fungsionalitas PC Component pada Modal Edit Asset
- **Penguncian Tipe Komponen (Component Type):** Jika tipe komponen PC (misal VGA) sudah ditentukan saat pembuatan awal, pilihan tipe komponen lain akan disembunyikan dan dinonaktifkan saat modal Edit dibuka. Hanya tipe aset bersangkutan yang akan ditampilkan.
- **Penambahan Tipe HDD:** Menambahkan opsi **HDD** ke dalam daftar pilihan tipe komponen PC (`component_type`).
- **Penyesuaian Baris Input:**
  - Menghilangkan kolom input "Kode Inventaris" khusus untuk aset berkategori PC Component.
  - Menambahkan baris input "Spesifikasi" tepat di bawah nama aset pada modal Edit.

---

## 5. Perbaikan Bug (Hotfix)
- **Fix Tombol Create Transfer Request:** Memperbaiki kesalahan sintaksis JavaScript (kurang tutup kurung kurawal `}`) pada script di halaman index mutasi (`transfer-requests/index.blade.php`) yang sempat membuat modal pembuatan request transfer tidak mau terbuka saat tombol diklik.
- **Menyembunyikan Tombol Edit Kode Inventaris:** Menghilangkan tombol aksi edit kode inventaris jika kategori aset yang bersangkutan adalah PC Component.

---

## 6. Penyempurnaan Mutasi Aset, Form Modal & QR Scanner
- **Dropdown Field Source:** Mengubah input tipe teks `Source` pada modal pembuatan dan pengeditan aset menjadi dropdown selector dengan pilihan tetap: **"Pengadaan"** dan **"Pembelian"**.
- **QR Scanner & Kamera Terjemahan Otomatis:**
  - Membuat baris input Kode Inventaris (serial number) menjadi `readonly` (tidak bisa diketik manual).
  - Menambahkan tombol ikon kamera (`📷`) di sebelah kanan input Kode Inventaris. Jika diklik, kamera aktif memindai QR Code menggunakan pustaka `html5-qrcode` lewat overlay modal, lalu secara otomatis mengisi input tersebut dengan hasil pemindaian.
- **Pengecualian PC Component:** Menghilangkan aksi QR Code dan kolom input Kode Inventaris untuk aset berkategori **PC Component** (karena kategori ini tidak memiliki kode inventaris unik sendiri).
- **Redesain Modal Create Transfer Request:**
  - Menghapus tombol kategori global dan dropdown pilihan PC global.
  - Mengimplementasikan tata letak berbasis **Category Cards**. Pengguna dapat menambahkan kartu kategori secara dinamis, memilih PC (opsional untuk komponen PC), dan menambahkan beberapa aset ke dalam masing-masing kartu kategori.
- **Redesain Detail Transfer Request & Tabel SPV:**
  - Memisahkan tampilan daftar aset di dalam modal detail transfer menjadi tabel-tabel terpisah berdasarkan kategorinya dengan judul yang jelas.
  - Mengimplementasikan alur **Partial Approval** (persetujuan sebagian) oleh SPV di mana jumlah unit yang disetujui dapat diisi secara dinamis antara nilai persetujuan saat ini hingga jumlah pengajuan maksimal. Jumlah unit yang disetujui tidak dapat dikurangi setelah disimpan (hanya dapat ditingkatkan).
  - Mengizinkan item berkondisi **Partial** untuk diproses ulang oleh SPV guna menyetujui tambahan unit baru, menghitung perbedaan stok secara otomatis, dan memperbarui mutasi stok.
- **Perbaikan Bug Penambahan Aset Lab:** Memperbaiki ketidaksesuaian validasi backend dan frontend terkait properti serialization (`isSerialized`) sehingga aset kategori **electronic**, **pc**, dan **non-electronic** terdeteksi sebagai aset berserial secara konsisten, sedangkan kategori **component-pc** dibebaskan dari kewajiban serialisasi saat ditambahkan ke dalam laboratorium.
