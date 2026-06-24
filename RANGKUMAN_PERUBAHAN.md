# Rangkuman Perbaikan (Changelog)

Dokumen ini merangkum semua perubahan dan fitur yang telah dikerjakan sesuai dengan permintaan revisi.

## 1. Perbaikan Bug & Tampilan (UI/UX)
- **Fix Export User (Lab Kosong):** Perbaikan pada `UserController` dan `UserExport`. Sekarang export data User tidak akan *error* jika terdapat user yang tidak memiliki lab (contohnya SPV). Terdapat opsi filter baru di menu User untuk menampilkan "Tanpa Lab (Lab Kosong)" yang mana filternya juga terbaca saat diexport.
- **Fix Darkmode Teks Staff Lab:** Mengganti styling *inline* dengan variabel Tailwind CSS yang adaptif (misal: `dark:text-gray-100` dan `dark:bg-slate-800`) pada `header.blade.php` di menu Laboratory agar tulisan staf lab terlihat jelas dan rapi saat Darkmode aktif.
- **Kolom Profil & Role User:** Memperbaiki desain tabel pada menu User (`user/index.blade.php`). Kolom "Name" sekarang menampilkan *avatar* atau profil inisial lengkap dengan email di bawahnya. Kolom "Role" juga kini tampil dengan *badge* warna-warni yang membedakan jabatan secara visual (misal: ungu untuk SPV, biru untuk PIC, dsb).
- **Tampilan Tabel Modal Request Item:** Merapikan *header* tabel dengan menyesuaikan kolom (mengembalikan *Serial Numbers* yang sebelumnya hilang) serta logika untuk menyembunyikan *wrapper* kategori secara keseluruhan jika tidak ada barang (misalnya jika hanya ada Mouse, kategori PC Asus dan Elektronik yang kosong tidak akan tampil menuhin layar).

## 2. Fitur Baru (Action & Logika)
- **Approve/Reject dengan Tombol Check & Cross:** Mempermudah SPV dalam melakukan ACC atau penolakan *request item*. Dropdown pada menu SPV *Request Lab* telah diganti menjadi dua tombol *iconic* (Centang Hijau untuk ACC dan Silang Merah untuk Reject), sehingga lebih cepat dan tidak ribet saat harus merespon banyak item.
- **Transfer Asset Sesuai Origin Lab:** Mengubah agar asal (*origin*) mutasi asest terikat/terkunci pada lab tempat SPV/Staff bernaung (menghapus *dropdown* yang sebelumnya memungkinkan pemindahan lab orang lain secara bebas).
- **Filter Sort Eksklusif SPV:** Menambahkan/memindahkan fitur sort di halaman admin agar sinkron dengan yang ada di SPV (khusus SPV saja).

*(Dokumen ini dibuat agar mudah diperiksa kembali)*
