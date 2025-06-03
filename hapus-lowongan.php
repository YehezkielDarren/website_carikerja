<?php
session_start();
require_once 'connection.php';

// 1. Autentikasi dan Autorisasi Pengguna
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'perusahaan') {
    header("Location: login.php"); // Redirect ke halaman login jika tidak sesuai
    exit();
}

if ($_SESSION['role']=="pencari_kerja"){
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['id']; // ID perusahaan yang sedang login

// 2. Validasi Input ID Lowongan
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Jika ID tidak ada atau bukan angka, redirect dengan pesan error
    header("Location: dashboard-company.php?hapus_status=gagal_invalid_id");
    exit();
}

$lowongan_id = mysqli_real_escape_string($conn, $_GET['id']);

// 3. Cek Kepemilikan Lowongan dan Ambil Nama Lowongan
$sql_cek_lowongan = "SELECT nama_pekerjaan FROM lowongan WHERE id = '$lowongan_id' AND perusahaan_id = '$user_id'";
$result_cek_lowongan = mysqli_query($conn, $sql_cek_lowongan);

if (!$result_cek_lowongan) {
    // Gagal query
    header("Location: dashboard-company.php?hapus_status=gagal_query");
    exit();
}

if (mysqli_num_rows($result_cek_lowongan) == 0) {
    // Lowongan tidak ditemukan atau bukan milik perusahaan ini
    header("Location: dashboard-company.php?hapus_status=gagal_auth");
    exit();
}
// $lowongan_data = mysqli_fetch_assoc($result_cek_lowongan);
// $nama_lowongan_untuk_pesan = $lowongan_data['nama_pekerjaan'];

// 4. Validasi Keberadaan Pelamar
// Asumsi tabel lamaran bernama 'lamaran' dan memiliki kolom 'lowongan_id'
$sql_cek_pelamar = "SELECT COUNT(*) as jumlah_pelamar FROM lamaran WHERE lowongan_id = '$lowongan_id'";
$result_cek_pelamar = mysqli_query($conn, $sql_cek_pelamar);

if ($result_cek_pelamar) {
    $data_pelamar = mysqli_fetch_assoc($result_cek_pelamar);
    if ($data_pelamar['jumlah_pelamar'] > 0) {
        // Jika ada pelamar, tidak bisa dihapus
        // Anda bisa menambahkan nama lowongan ke URL jika ingin menampilkannya di pesan
        // header("Location: dashboard-company.php?hapus_status=gagal_ada_pelamar&nama_lowongan=" . urlencode($nama_lowongan_untuk_pesan));
        header("Location: dashboard-company.php?hapus_status=gagal_ada_pelamar");
        exit();
    }
} else {
    // Gagal query cek pelamar
    header("Location: dashboard-company.php?hapus_status=gagal_query");
    exit();
}

// 5. Proses Penghapusan Lowongan
// Tambahan: Anda mungkin ingin menghapus data terkait lainnya terlebih dahulu jika ada foreign key constraints
// Misalnya, jika ada tabel 'notifikasi_pelamar' yang terkait dengan 'lowongan', hapus dari sana dulu.

$sql_hapus = "DELETE FROM lowongan WHERE id = '$lowongan_id' AND perusahaan_id = '$user_id'";
if (mysqli_query($conn, $sql_hapus)) {
    // Berhasil menghapus
    header("Location: dashboard-company.php?hapus_status=success");
    exit();
} else {
    // Gagal menghapus
    header("Location: dashboard-company.php?hapus_status=gagal_query");
    exit();
}

?>