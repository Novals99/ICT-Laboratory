Konteks Proyek: ICT Inventory System
1. Gambaran Umum Proyek
Tujuan

ICT Inventory System adalah aplikasi berbasis web yang dirancang untuk mengelola, melacak, dan mendistribusikan aset IT, komponen PC, serta peralatan laboratorium. Sistem ini menyediakan manajemen siklus hidup aset secara menyeluruh, mulai dari pengadaan (Request), penempatan (Transfer/Laboratorium), pemeliharaan (Return), hingga pelacakan melalui pemindaian QR Code.

Alur Bisnis Utama
Pendaftaran Aset
Aset didaftarkan ke dalam sistem (Elektronik, Non-Elektronik, PC, dan Komponen PC).
Pembuatan Nomor Seri
Aset elektronik dan komponen PC mendapatkan Nomor Seri unik beserta QR Code yang dibuat secara otomatis.
Distribusi & Pemetaan
Aset dapat ditempatkan pada laboratorium tertentu atau dipetakan ke dalam PC tertentu (contohnya RAM dipasang pada PC-01).
Logistik
Staff dapat mengajukan permintaan aset, melakukan mutasi antar laboratorium, atau mengembalikan aset yang rusak maupun tidak digunakan.
Pelacakan
Pengguna dapat memindai QR Code menggunakan kamera perangkat atau mengunggah gambar QR Code untuk melihat status, kondisi, dan lokasi aset secara instan.
Teknologi yang Digunakan
Backend: Laravel 12, PHP 8.2
Database: MySQL / SQLite
Frontend: Blade Templates, TailwindCSS, Livewire
Admin Panel: Filament PHP (v3)
Utility Library:
endroid/qr-code (Pembuatan QR Code di Backend)
html5-qrcode & jsQR (Pemindaian QR Code di Frontend)
barryvdh/laravel-dompdf (Ekspor PDF)
2. Fitur Saat Ini
Dashboard (DashboardController)

Memberikan gambaran umum terkait:

Total aset
Kondisi aset (Baik, Rusak, Hilang)
Log aktivitas terbaru
Manajemen Aset (AssetController, SerialNumberController)
CRUD aset dengan SKU yang dibuat otomatis.
Pembuatan Nomor Seri detail untuk kategori aset yang memerlukan pelacakan.
Scan Code (ScanCodeController, QrCodeController)
Membaca QR Code secara instan melalui kamera maupun unggah gambar.
Menampilkan detail aset dan nomor seri apabila ditemukan kecocokan.
Menggunakan QrCodeController untuk menghasilkan gambar QR Code PNG melalui endpoint /qr?text=....
Manajemen Laboratorium & PC (LaboratoryController, PcController)
Mengelola data laboratorium.
Menghubungkan PC dengan laboratorium.
Mendukung pemetaan komponen PC ke slot tertentu.
Request & Transfer (RequestLabController, TransferRequestController, ReturnRequestController)
Menyediakan alur lengkap perpindahan aset.
Mendukung proses persetujuan (approve) dan penolakan (reject).
Activity & Asset Logging (ActivityLogController, AssetLogController)
Menyimpan riwayat audit lengkap untuk setiap perpindahan aset maupun perubahan kondisi.
3. Struktur Database
Tabel Utama
users

Menyimpan data pengguna dan autentikasi.

role (Admin, PIC, SPV, dll.)
laboratories

Menyimpan data laboratorium.

lab_name
location
assets

Data utama aset.

sku
asset_name
asset_category
total_asset
asset_serial_numbers

Menyimpan data unit aset individual.

asset_id
serial_number
condition
status
slot
pcs

Menyimpan data workstation yang terhubung ke laboratorium.

lab_id
pc_name
Tabel Transaksi
Request
request_labs
request_items

Digunakan untuk pengajuan kebutuhan atau permintaan aset.

Transfer
transfer_requests
transfer_request_items

Digunakan untuk perpindahan aset antar laboratorium.

Return
return_requests
return_request_items

Digunakan untuk pengembalian aset ke gudang atau inventaris utama.

Log
asset_logs
activity_logs

Digunakan untuk pencatatan riwayat aktivitas yang tidak dapat diubah.

Relasi Utama
Asset memiliki banyak (HasMany) AssetSerialNumber.
Pc dimiliki oleh (BelongsTo) Laboratory.
AssetSerialNumber dimiliki oleh (BelongsTo) Laboratory.
AssetSerialNumber juga dapat dimiliki oleh (BelongsTo) Pc melalui mekanisme pemetaan.
4. Autentikasi & Otorisasi
Middleware

Aplikasi menggunakan middleware bawaan Laravel yaitu auth untuk melindungi seluruh halaman dashboard internal.

Hak Akses Berdasarkan Role

Hak akses dikelola melalui kolom role pada tabel users.

Contoh:

spv inventory
pic
staff
Route Publik

Route berikut dapat diakses tanpa login:

/login
/qr

Endpoint /qr dibuat publik agar tag <img> pada PDF maupun modal dapat mengambil gambar QR Code tanpa kendala token autentikasi atau masalah CORS.

5. Perubahan Terbaru (Sesi Saat Ini)
Refaktor Logika Pemindaian QR Code (scan-code/index.blade.php)
Mengganti Scanner Upload yang Bermasalah
Scanner upload milik html5-qrcode diganti dengan jsQR.
html5-qrcode mengalami kendala saat membaca PNG hasil generate server akibat bug rendering canvas tersembunyi.
jsQR membaca langsung dari ImageData sehingga proses decoding gambar menjadi lebih cepat dan stabil.
Perubahan Terminologi
Seluruh penggunaan istilah "Barcode" diubah menjadi "QR Code" agar sesuai dengan fungsi sebenarnya.
Pesan Error Kustom

Sistem kini menampilkan pesan:

"QR Code tidak sesuai"

apabila QR Code valid berhasil dipindai namun datanya tidak ditemukan di database.

Pemindahan Pembuatan QR Code ke Backend (QrCodeController.php)
Menghilangkan masalah rendering QR Code di frontend.
QR Code kini sepenuhnya dibuat di server menggunakan endroid/qr-code.
Script CDN lama (qrcode.js) telah dihapus dari halaman aset.
6. Perbaikan Bug & Penyempurnaan Tampilan Sebelumnya
Perbaikan Export User (Lab Kosong)
Memperbaiki UserController dan UserExport.
Export data user tidak lagi gagal apabila terdapat pengguna tanpa laboratorium (contohnya SPV).
Menambahkan filter "Tanpa Lab (Lab Kosong)" yang juga berlaku saat export.
Perbaikan Dark Mode pada Data Staff Laboratorium
Mengganti styling inline menjadi class Tailwind yang responsif terhadap dark mode.
Contoh:
dark:text-gray-100
dark:bg-slate-800
Penyempurnaan Tampilan Profil dan Role User
Kolom Name kini menampilkan:
Avatar atau inisial pengguna
Email pengguna
Kolom Role menggunakan badge berwarna sesuai jabatan:
Ungu untuk SPV
Biru untuk PIC
dan warna lainnya sesuai kebutuhan
Perbaikan Tabel Modal Request Item
Mengembalikan kolom Serial Numbers yang sempat hilang.
Menyembunyikan kategori secara otomatis apabila tidak terdapat item di dalamnya.
7. Fitur Baru (Aksi & Logika)
Approve/Reject Menggunakan Tombol Ikon
Dropdown persetujuan pada menu Request Lab diganti menjadi:
Tombol Centang Hijau (Approve)
Tombol Silang Merah (Reject)
Mutasi Aset Berdasarkan Laboratorium Asal
Laboratorium asal aset kini otomatis mengikuti laboratorium milik SPV/PIC/Staff yang sedang login.
Menghapus dropdown yang sebelumnya memungkinkan perpindahan aset dari laboratorium lain secara bebas.
Filter Sort Khusus SPV
Menambahkan fitur sorting khusus SPV.
Menyamakan perilaku filter dan sorting dengan halaman admin.
8. Tugas yang Masih Pending & Rekomendasi Pengembangan
Fitur yang Belum Selesai
Pemetaan Komponen PC yang Lebih Interaktif
Antarmuka drag-and-drop untuk pemasangan komponen ke slot PC.
Cetak QR Code Massal
Ekspor PDF yang dapat mencetak banyak QR Code sekaligus berdasarkan nomor seri.
Kendala yang Diketahui
Scanner Kamera
html5-qrcode memerlukan:
HTTPS, atau
localhost

agar browser mengizinkan akses kamera perangkat.

Technical Debt
Beberapa halaman Blade kustom pada folder pages/ berpotensi dimigrasikan ke Resource Filament agar seluruh panel admin memiliki standar yang sama.
9. Arsitektur Proyek
app/Models/

Berisi model Eloquent dengan:

Relasi yang terdefinisi jelas
Properti $fillable yang aman
app/Http/Controllers/

Berisi controller MVC yang menangani logika bisnis untuk halaman Blade kustom.

resources/views/pages/

Berisi antarmuka utama berbasis TailwindCSS yang telah dikustomisasi.

resources/views/panel/

Berisi layout utama seperti:

Sidebar
Navbar
Wrapper Content
routes/web.php

Berisi definisi route yang telah dikelompokkan berdasarkan middleware autentikasi.

10. Panduan Instalasi
1. Clone Repository & Install Dependency
composer install
npm install
2. Konfigurasi Environment
cp .env.example .env
php artisan key:generate
3. Setup Database

Pastikan MySQL atau SQLite sudah berjalan dan dikonfigurasi pada file .env.

php artisan migrate --seed
4. Menjalankan Aplikasi
php artisan serve
npm run dev
11. Catatan Developer
QR Code Tidak Disimpan sebagai Gambar di Database

Jangan menyimpan file PNG QR Code ke dalam database.

Yang disimpan hanya nilai:

serial_number

Gambar QR Code dibuat secara dinamis melalui endpoint:

/qr?text=[SERIAL_NUMBER]

Pendekatan ini:

Lebih ringan
Lebih cepat
Mengurangi ukuran database secara signifikan
Mekanisme Scan QR Menggunakan jsQR

Jika ingin memodifikasi fitur upload QR Code:

Gambar yang diunggah digambar ke dalam <canvas> tersembunyi.
ImageData dari canvas dikirim ke jsQR.
jsQR melakukan proses decoding.

Penting:

Jangan menggunakan:

display: none;

karena beberapa browser (terutama WebKit) akan menghapus data piksel untuk menghemat memori sehingga scanner gagal bekerja.

Gunakan:

position: absolute;
top: -9999px;
visibility: hidden;
Standar Styling

Proyek menggunakan kombinasi:

CSS Variables
TailwindCSS

dengan konsep desain:

Glassmorphism
Subtle Gradient
Token warna seperti var(--bg-card)

Saat menambahkan komponen baru, gunakan token desain yang sudah tersedia agar tampilan tetap konsisten di seluruh aplikasi.