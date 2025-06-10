-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2025 at 01:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `carikerjaweb_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `lamaran`
--

CREATE TABLE `lamaran` (
  `id_lamaran` int(11) NOT NULL,
  `lowongan_id` int(11) NOT NULL,
  `pelamar_id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `cv` varchar(255) NOT NULL,
  `portofolio` varchar(255) DEFAULT NULL,
  `surat_lamaran` varchar(255) DEFAULT NULL,
  `tanggal_lamaran` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lamaran`
--

INSERT INTO `lamaran` (`id_lamaran`, `lowongan_id`, `pelamar_id`, `nama_lengkap`, `tanggal_lahir`, `email`, `no_hp`, `cv`, `portofolio`, `surat_lamaran`, `tanggal_lamaran`) VALUES
(2, 7, 1, 'Rina Andriani', '1998-07-14', 'rina@mail.com', '081234567890', 'uploads/cv_Rina_Andriani__1749100894.pdf', NULL, 'uploads/lamaran_Rina_Andriani__1749100894.pdf', '2025-06-05'),
(3, 2, 2, 'Budi Santoso', '1995-03-22', 'budi@mail.com', '082112345678', 'uploads/cv_Budi_Santoso__1749104462.pdf', 'uploads/portofolio_Budi_Santoso__1749104462.pdf', 'uploads/lamaran_Budi_Santoso__1749104462.pdf', '2025-06-05'),
(4, 10, 1, 'Rina Andriani', '1998-07-14', 'rina@mail.com', '081234567890', 'uploads/cv_Rina_Andriani__1749106119.pdf', 'uploads/portofolio_Rina_Andriani__1749106119.pdf', 'uploads/lamaran_Rina_Andriani__1749106119.pdf', '2025-06-05');

-- --------------------------------------------------------

--
-- Table structure for table `lowongan`
--

CREATE TABLE `lowongan` (
  `id` int(11) NOT NULL,
  `perusahaan_id` int(11) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `nama_pekerjaan` varchar(100) DEFAULT NULL,
  `jenis_pekerjaan` enum('Full-time','Part-time','Remote','Freelance') DEFAULT NULL,
  `lokasi` text DEFAULT NULL,
  `gaji` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `syarat` text DEFAULT NULL,
  `tanggal_batas` date DEFAULT NULL,
  `isPorto` tinyint(1) DEFAULT NULL,
  `tanggal_buat` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lowongan`
--

INSERT INTO `lowongan` (`id`, `perusahaan_id`, `kategori`, `nama_pekerjaan`, `jenis_pekerjaan`, `lokasi`, `gaji`, `deskripsi`, `syarat`, `tanggal_batas`, `isPorto`, `tanggal_buat`) VALUES
(1, 1, 'IT', 'Frontend Developer', 'Full-time', 'Jakarta', 'Rp12.500.000', 'Sebagai Frontend Developer, Anda akan bertanggung jawab untuk merancang, mengembangkan, dan mengoptimalkan antarmuka pengguna (UI) aplikasi web perusahaan. Anda akan bekerja sama dengan tim desain dan backend untuk memastikan pengalaman pengguna yang responsif dan intuitif. Selain itu, Anda juga akan memastikan kode tetap bersih, terstruktur, dan mudah dirawat.', 'Pengalaman minimal 2 tahun sebagai Frontend Developer; Menguasai HTML5, CSS3, JavaScript (ES6+), dan React; Pengalaman dengan framework tambahan seperti Next.js atau Vue.js merupakan nilai plus; Mampu menggunakan tools version control seperti Git; Memahami prinsip UI/UX dan responsive design; Mampu bekerja dalam tim dan berkomunikasi dengan baik; Mampu bekerja dalam deadline ketat dan lingkungan agile', '2025-04-30', 1, '2025-04-01'),
(2, 2, 'IT', 'Android Developer', 'Remote', 'Bandung', 'Rp8.000.000', 'Kami mencari Android Developer berbakat untuk membangun dan memelihara aplikasi Android berkinerja tinggi untuk klien-klien kami. Anda akan bekerja dalam tim pengembangan untuk menerjemahkan kebutuhan bisnis ke dalam aplikasi mobile yang modern, aman, dan skalabel. Posisi ini bersifat remote, dengan komunikasi melalui platform kolaborasi daring.', 'Pengalaman minimal 2 tahun dalam pengembangan aplikasi Android; Menguasai Kotlin dan Java; Memiliki pengalaman menggunakan Firebase (Auth, Firestore, Cloud Messaging); Memahami prinsip MVVM/MVP dan Clean Architecture; Mampu menggunakan Android Studio dan alat debugging; Familiar dengan Git dan CI/CD tools (seperti Bitrise, GitHub Actions); Pernah mempublikasikan aplikasi ke Google Play Store; Mampu bekerja mandiri dan disiplin dalam remote environment', '2025-07-15', 1, '2025-06-15'),
(3, 2, 'Pendidikan', 'Guru Matematika SMA', 'Part-time', 'Surabaya', 'Rp4.000.000', 'Mengajar mata pelajaran Matematika untuk jenjang SMA kelas 10-12', 'Minimal S1 Pendidikan Matematika, Memiliki pengalaman mengajar Matematika SMA minimal 1 tahun; Mampu menyusun RPP dan materi ajar secara mandiri; Familiar dengan platform pembelajaran digital (Google Classroom, Zoom); Sabar, komunikatif, dan menyukai dunia pendidikan; Bersedia mengikuti pelatihan internal; Diutamakan yang berdomisili di area Surabaya dan sekitarnya; Memiliki kemampuan analisis soal dan logika matematika yang kuat', '2025-07-25', 0, '2025-06-25'),
(4, 1, 'Kesehatan', 'Perawat Homecare', 'Full-time', 'Depok', 'Rp7.500.000', 'Perawat Homecare bertanggung jawab memberikan pelayanan keperawatan langsung kepada pasien di rumah mereka. Ini mencakup pemberian obat, perawatan luka, pemantauan kondisi vital, edukasi pasien dan keluarga, serta pencatatan perkembangan pasien. Kami mencari perawat yang tidak hanya kompeten secara teknis, tetapi juga memiliki empati tinggi dan mampu bekerja secara mandiri di lapangan.', 'Lulusan D3 atau S1 Keperawatan dengan STR aktif; Memiliki pengalaman minimal 1 tahun sebagai perawat homecare atau di RS; Mampu melakukan tindakan keperawatan dasar dan lanjutan; Bersedia bekerja shift dan melakukan kunjungan ke rumah pasien; Memiliki kendaraan pribadi dan SIM C/A; Komunikatif, ramah, dan berpenampilan profesional; Siap bekerja di wilayah Jabodetabek; Mampu mengoperasikan aplikasi pencatatan medis berbasis digital', '2025-08-10', 0, '2025-07-10'),
(5, 1, 'Keuangan', 'Analis Keuangan', 'Full-time', 'Jakarta', 'Rp9.000.000', 'Bertanggung jawab dalam menganalisis laporan keuangan, mengevaluasi kinerja keuangan perusahaan, serta memberikan rekomendasi investasi dan strategi pengeluaran. Berkolaborasi dengan tim akuntansi dan manajemen untuk mendukung perencanaan anggaran tahunan.', 'S1 Ekonomi/Akuntansi/Manajemen Keuangan; Pengalaman minimal 2 tahun di bidang keuangan; Mahir menggunakan Excel dan software akuntansi (SAP/Oracle); Kemampuan analitis dan presentasi yang kuat; Teliti dan mampu bekerja di bawah tekanan', '2025-08-20', 0, '2025-07-20'),
(6, 1, 'Pemasaran', 'Digital Marketing Specialist', 'Full-time', 'Bandung', 'Rp7.500.000', 'Mengelola kampanye digital (SEO, SEM, media sosial), menganalisis kinerja iklan digital, dan mengembangkan strategi konten untuk meningkatkan awareness dan konversi. Bekerja erat dengan tim kreatif dan konten.', 'Pengalaman minimal 2 tahun di bidang digital marketing; Mahir menggunakan Google Ads, Meta Ads, dan Google Analytics; Familiar dengan SEO dan tools seperti SEMrush, Ahrefs; Kreatif, analitis, dan berorientasi pada hasil; Lulusan jurusan Komunikasi, Pemasaran, atau sejenisnya', '2025-08-25', 1, '2025-07-25'),
(7, 1, 'Logistik', 'Manajer Gudang', 'Full-time', 'Jakarta Pusat', 'Rp8.000.000', 'Bertanggung jawab mengelola operasi gudang, alur keluar-masuk barang, serta memastikan ketersediaan stok. Melakukan koordinasi dengan tim logistik dan pembelian untuk efisiensi rantai pasok.', 'Pengalaman sebagai kepala gudang min. 3 tahun; Memahami sistem inventory dan manajemen logistik; Mahir menggunakan sistem ERP atau software gudang; Kuat dalam manajemen tim dan waktu; Lulusan minimal D3 Logistik atau setara', '2025-09-05', 0, '2025-08-05'),
(8, 1, 'Hiburan', 'Video Editor', 'Remote', 'Online', 'Rp6.000.000', 'Bertanggung jawab menyunting video untuk keperluan promosi, media sosial, dan dokumentasi. Mampu mengemas video secara kreatif sesuai brief dan brand guidelines perusahaan.', 'Menguasai Adobe Premiere, After Effects, dan tools editing lainnya; Portofolio editing video kreatif; Memahami storytelling visual; Bisa bekerja sesuai deadline; Komunikatif dan kooperatif dalam tim remote', '2025-08-30', 1, '2025-08-01'),
(9, 2, 'Hiburan', 'Content Creator Tiktok', 'Part-time', 'Surabaya', 'Rp4.500.000', 'Membuat konten video, gambar, dan tulisan untuk media sosial. Menyusun ide konten yang menarik dan sesuai tren untuk meningkatkan engagement audiens.', 'Kreatif dan aktif di media sosial, Menguasai Canva, CapCut, atau software editing lainnya; Memahami algoritma Instagram/TikTok; Mampu membuat caption menarik; Disiplin dan mampu membuat konten secara konsisten', '2025-09-01', 1, '2025-08-01'),
(10, 2, 'Keuangan', 'Staf Administrasi Keuangan', 'Full-time', 'Yogyakarta', 'Rp5.000.000', 'Menangani pencatatan keuangan harian, menyusun laporan bulanan, serta membantu proses audit dan pembayaran. Memastikan ketepatan dan kelengkapan dokumen keuangan.', 'Lulusan D3/S1 Akuntansi/Administrasi, Teliti dan terorganisir; Mampu menggunakan Excel dan software akuntansi; Komunikatif dan cepat belajar; Pengalaman 1 tahun di bidang administrasi menjadi nilai tambah', '2025-09-10', 1, '2025-08-10'),
(11, 2, 'Pendidikan', 'Tutor Bahasa Inggris', 'Part-time', 'Online / Zoom', 'Rp3.500.000', 'Mengajar Bahasa Inggris secara daring untuk siswa tingkat SD hingga SMA. Menyusun materi ajar, latihan soal, dan evaluasi hasil belajar secara berkala.', 'Lulusan S1 Sastra Inggris atau Pendidikan Bahasa Inggri; Fasih berbahasa Inggris lisan dan tulisan; Berpengalaman mengajar online; Bersedia mengajar di luar jam kerja (malam/weekend); Sabar, ramah, dan komunikatif', '2025-08-28', 1, '2025-07-28'),
(12, 2, 'Kesehatan', 'Asisten Apoteker', 'Full-time', 'Semarang', 'Rp4.800.000', 'Membantu Apoteker dalam pelayanan resep, pengelolaan obat, serta edukasi kepada pasien. Bertanggung jawab menjaga kebersihan dan ketertiban di area pelayanan farmasi.', 'Pendidikan minimal D3 Farmasi; STRTTK aktif; Teliti dan bertanggung jawab; Mampu menggunakan software apotek; Siap bekerja dalam shift', '2025-09-03', 1, '2025-08-03'),
(13, 3, 'IT', 'IT Support', 'Part-time', 'Bekasi', 'Kompetitif', 'Lorem ipsum dolor sit amet ......', 'Mahasiswa Informatika yang baru lulus; Siap bekerja di Bekasi; Wajib melampirkan portofolio; Mampu memahami dasar-dasar jaringan di perusahaan', '2025-06-12', 1, '2025-05-12'),
(14, 1, 'Desain', 'Graphic Desainer', 'Part-time', 'Online', 'Rp3.000.000', 'Lorem Ipsum', 'Lorem;Ipsum;dolor,sit,amet', '2025-06-13', 1, '2025-05-13');

-- --------------------------------------------------------

--
-- Table structure for table `pencari_kerja`
--

CREATE TABLE `pencari_kerja` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pencari_kerja`
--

INSERT INTO `pencari_kerja` (`id`, `user_id`, `email`, `nama_lengkap`, `tanggal_lahir`, `no_hp`, `foto`) VALUES
(1, 1, 'rina@mail.com', 'Rina Andriani', '1998-07-14', '081234567890', 'uploads_img/tam.png'),
(2, 2, 'budi@mail.com', 'Budi Santoso', '1995-03-22', '082112345678', NULL),
(3, 5, 'darren.tzy@gmail.com', 'Yehezkiel Darren', '2016-01-16', '087723128908', 'uploads_img/xaverius fam.jpg'),
(4, 6, 'mail@example.com', 'Hezekiah', '2012-01-02', '12311423', 'uploads_img/images.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `perusahaan`
--

CREATE TABLE `perusahaan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_perusahaan` varchar(100) DEFAULT NULL,
  `lokasi` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perusahaan`
--

INSERT INTO `perusahaan` (`id`, `user_id`, `nama_perusahaan`, `lokasi`, `logo`) VALUES
(1, 3, 'PT Maju Jaya', 'Jakarta, Indonesia', 'uploads_img/majujaya.png'),
(2, 4, 'CV Teknologi Hebat', 'Bandung, Indonesia', 'uploads_img/teknohbt.png'),
(3, 7, 'PT. SukaMaju', 'Jakarta Pusat', 'uploads_img/image-removebg-preview.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pencari_kerja','perusahaan') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'rina', 'rina123', 'pencari_kerja'),
(2, 'budi', 'budi123', 'pencari_kerja'),
(3, 'majujaya_hrd', 'maju123', 'perusahaan'),
(4, 'teknohbt_admin', 'hebat123', 'perusahaan'),
(5, 'darrenTzy', 'D4rrenCla1', 'pencari_kerja'),
(6, 'hezki', 'admin123', 'pencari_kerja'),
(7, 'sukamaju_admin', 'admin123_sukamaju', 'perusahaan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lamaran`
--
ALTER TABLE `lamaran`
  ADD PRIMARY KEY (`id_lamaran`),
  ADD KEY `fk_lowongan` (`lowongan_id`),
  ADD KEY `fk_pelamar_id` (`pelamar_id`);

--
-- Indexes for table `lowongan`
--
ALTER TABLE `lowongan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `perusahaan_id` (`perusahaan_id`);

--
-- Indexes for table `pencari_kerja`
--
ALTER TABLE `pencari_kerja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `perusahaan`
--
ALTER TABLE `perusahaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lamaran`
--
ALTER TABLE `lamaran`
  MODIFY `id_lamaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lowongan`
--
ALTER TABLE `lowongan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pencari_kerja`
--
ALTER TABLE `pencari_kerja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `perusahaan`
--
ALTER TABLE `perusahaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lamaran`
--
ALTER TABLE `lamaran`
  ADD CONSTRAINT `fk_lowongan` FOREIGN KEY (`lowongan_id`) REFERENCES `lowongan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pelamar_id` FOREIGN KEY (`pelamar_id`) REFERENCES `pencari_kerja` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lowongan`
--
ALTER TABLE `lowongan`
  ADD CONSTRAINT `lowongan_ibfk_1` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan` (`id`);

--
-- Constraints for table `pencari_kerja`
--
ALTER TABLE `pencari_kerja`
  ADD CONSTRAINT `pencari_kerja_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `perusahaan`
--
ALTER TABLE `perusahaan`
  ADD CONSTRAINT `perusahaan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;