# Proyek Aplikasi Web Pencarian Kerja

Proyek ini adalah aplikasi web yang dibangun menggunakan PHP untuk platform pencarian kerja. Aplikasi ini memungkinkan pencari kerja untuk melihat lowongan pekerjaan dan melamar pekerjaan, serta perusahaan untuk memposting lowongan pekerjaan.

## Fitur Utama

- Registrasi dan Login untuk Pencari Kerja dan Perusahaan.
- Pencarian dan Filter Lowongan Pekerjaan.
- Detail Lowongan Pekerjaan.
- Proses Aplikasi Lamaran oleh Pencari Kerja.
- Dashboard untuk Pencari Kerja (melihat riwayat lamaran, dll.).
- Dashboard untuk Perusahaan (mengelola lowongan, melihat pelamar, dll.).

## Fungsionalitas Web

### Untuk Pencari Kerja:
-   **Registrasi & Login**: Membuat akun dan masuk ke platform.
-   **Manajemen Profil**: Memperbarui informasi pribadi dan profesional.
-   **Pencarian Lowongan**: Mencari pekerjaan berdasarkan kata kunci, lokasi, kategori, dll.
-   **Melihat Detail Lowongan**: Mendapatkan informasi lengkap tentang suatu pekerjaan, termasuk deskripsi, syarat, dan informasi perusahaan.
-   **Melamar Pekerjaan**: Mengirimkan lamaran dengan mengisi form dan mengunggah dokumen pendukung (CV, portofolio, surat lamaran).
-   **Melihat Riwayat Lamaran**: Melacak status lamaran yang telah dikirim.

### Untuk Perusahaan:
-   **Registrasi & Login**: Membuat akun perusahaan dan masuk ke platform.
-   **Manajemen Profil Perusahaan**: Memperbarui informasi detail perusahaan.
-   **Memasang Lowongan**: Membuat dan mempublikasikan lowongan pekerjaan baru.
-   **Mengelola Lowongan**: Mengedit, menonaktifkan, atau menghapus lowongan yang sudah ada.
-   **Melihat Pelamar**: Meninjau daftar pelamar untuk setiap lowongan, beserta dokumen yang mereka kirimkan.

## Struktur Direktori Penting

- `public/`: Berisi file-file yang dapat diakses publik (CSS, JS, gambar, dan skrip PHP utama seperti `index.php`, `login.php`, `apply.php`).
- `public/uploads/`: Direktori tempat file-file yang diunggah oleh pengguna (CV, portofolio, surat lamaran) disimpan.
- `public/uploads_img/`: Direktori ini kemungkinan digunakan untuk menyimpan gambar-gambar terkait proyek, seperti logo perusahaan atau gambar profil default.
- `src/`: Berisi kode sumber inti aplikasi.
- `src/includes/`: Berisi file-file yang sering disertakan seperti koneksi database (`connection.php`) dan fungsi bantuan (`helpers.php`).
