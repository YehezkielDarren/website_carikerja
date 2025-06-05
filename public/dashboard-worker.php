<?php
session_start();
require_once '../src/includes/connection.php';

// Helper function for formatting date (can be moved to a global functions file)
if (!function_exists('formatTanggal')) {
    function formatTanggal($tanggalInput) {
        if (empty($tanggalInput) || $tanggalInput === '0000-00-00' || $tanggalInput === '0000-00-00 00:00:00') {
            return 'N/A';
        }
        try {
            $date = new DateTime($tanggalInput);
            $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
            if (class_exists('IntlDateFormatter')) {
                $formatter = new IntlDateFormatter(
                    'id_ID',
                    IntlDateFormatter::LONG,
                    IntlDateFormatter::NONE,
                    'Asia/Jakarta',
                    IntlDateFormatter::GREGORIAN,
                    'd MMMM yyyy'
                );
                return $formatter->format($date);
            } else {
                return $date->format('d M Y'); // Fallback: 18 May 2025
            }
        } catch (Exception $e) {
            error_log("Error formatting date '$tanggalInput': " . $e->getMessage());
            return $tanggalInput;
        }
    }
}

// 1. Login Requirement
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 2. Role Access Control
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pencari_kerja') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'perusahaan') {
        header("Location: dashboard-company.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$pencari_kerja_id = $_SESSION['id'];
$nama_pengguna = $_SESSION['username'];
$logo_pengguna = $_SESSION['logo'] ?? 'img/ProfilePicture.jpg'; // Default if logo not set

$total_lowongan_aktif = 0;
$jumlah_lamaran_user = 0;
$applied_jobs = [];
$pesan_info = "";

// 3. Total active job vacancies on the platform
$sql_total_lowongan = "SELECT COUNT(id) AS total_lowongan_aktif FROM lowongan WHERE tanggal_batas > NOW()";
$result_total_lowongan = mysqli_query($conn, $sql_total_lowongan);
if ($result_total_lowongan && mysqli_num_rows($result_total_lowongan) > 0) {
    $row_total = mysqli_fetch_assoc($result_total_lowongan);
    $total_lowongan_aktif = $row_total['total_lowongan_aktif'];
}

// 4. Total jobs applied for by the current user
$sql_jumlah_lamaran = "SELECT COUNT(id_lamaran) AS jumlah_lamaran_user FROM lamaran WHERE pelamar_id = ?";
$stmt_jumlah_lamaran = mysqli_prepare($conn, $sql_jumlah_lamaran);
if ($stmt_jumlah_lamaran) {
    mysqli_stmt_bind_param($stmt_jumlah_lamaran, "i", $pencari_kerja_id);
    mysqli_stmt_execute($stmt_jumlah_lamaran);
    $result_jumlah = mysqli_stmt_get_result($stmt_jumlah_lamaran);
    if ($result_jumlah && mysqli_num_rows($result_jumlah) > 0) {
        $row_jumlah = mysqli_fetch_assoc($result_jumlah);
        $jumlah_lamaran_user = $row_jumlah['jumlah_lamaran_user'];
    }
    mysqli_stmt_close($stmt_jumlah_lamaran);
}

// Fetch list of jobs the user has applied to
$sql_applied_jobs = "SELECT
                        lamaran.id_lamaran,
                        lamaran.tanggal_lamaran,
                        lowongan.nama_pekerjaan,
                        lowongan.id AS lowongan_id,
                        perusahaan.nama_perusahaan
                    FROM lamaran
                    JOIN lowongan ON lamaran.lowongan_id = lowongan.id
                    JOIN perusahaan ON lowongan.perusahaan_id = perusahaan.id
                    WHERE lamaran.pelamar_id = ?
                    ORDER BY lamaran.tanggal_lamaran DESC";
$stmt_applied_jobs = mysqli_prepare($conn, $sql_applied_jobs);
if ($stmt_applied_jobs) {
    mysqli_stmt_bind_param($stmt_applied_jobs, "i", $pencari_kerja_id);
    mysqli_stmt_execute($stmt_applied_jobs);
    $result_applied_jobs = mysqli_stmt_get_result($stmt_applied_jobs);
    while ($row = mysqli_fetch_assoc($result_applied_jobs)) {
        $applied_jobs[] = $row;
    }
    mysqli_stmt_close($stmt_applied_jobs);
}

if (empty($applied_jobs) && $jumlah_lamaran_user == 0) {
    $pesan_info = "Anda belum melamar pekerjaan apapun.";
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pencari Kerja - Cari Kerja.com</title>
    <link rel="stylesheet" href="assets/css/dashboard-worker.css">
    <link rel="stylesheet" href="assets/css/global-styles.css">
    <link rel="stylesheet" href="assets/css/time.css" />
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
    <script src="assets/js/time.js"></script>
</head>
<body>
    <header>
      <div class="logo">
        <img src="img/LogoHeader1.png" alt="logokerja" />
        <a>Cari Kerja. <span class="small">com</span></a>
      </div>
      <nav>
        <ul type="none">
          <li><a href="index.php">Home</a></li>
          <span class="separator"></span>
          <li><a href="logout.php">Logout</a></li>
        </ul>
        <a href="dashboard-worker.php"> <!-- Link to self or a profile edit page -->
          <?php
            if(isset($logo_pengguna) && !empty($logo_pengguna) && file_exists(ltrim($logo_pengguna, '/'))) {
              echo '<img src="' . htmlspecialchars($logo_pengguna) . '" alt="profilepict" class="profilepicture" />';
            } else {
              echo '<img src="img/ProfilePicture.jpg" alt="profilepict" class="profilepicture" />';
            }
          ?>
        </a>
      </nav>
    </header>

    <div class="clock-container">
      <div class="clock-time" id="clock">00:00:00</div>
      <div class="clock-date" id="date">Loading date...</div>
    </div>

    <main>
        <section class="dashboard-header">
            <h1>Dashboard Anda</h1>
            <p>Selamat datang kembali, <?php echo htmlspecialchars($nama_pengguna); ?>!</p>
        </section>

        <section class="dashboard-stats">
            <div class="stat-card">
                <h2>Total Lowongan Aktif</h2>
                <p><?php echo $total_lowongan_aktif; ?></p>
                <a href="index.php" class="btn-link">Cari Lowongan Sekarang</a>
            </div>
            <div class="stat-card">
                <h2>Lamaran Terkirim</h2>
                <p><?php echo $jumlah_lamaran_user; ?></p>
            </div>
        </section>

        <section class="applied-jobs-list">
            <h2>Riwayat Lamaran Anda</h2>
            <?php if (!empty($pesan_info)): ?>
                <p class="info-message"><?php echo htmlspecialchars($pesan_info); ?></p>
            <?php elseif (!empty($applied_jobs)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nama Pekerjaan</th>
                            <th>Perusahaan</th>
                            <th>Tanggal Melamar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applied_jobs as $job): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['nama_pekerjaan']); ?></td>
                                <td><?php echo htmlspecialchars($job['nama_perusahaan']); ?></td>
                                <td><?php echo htmlspecialchars(formatTanggal($job['tanggal_lamaran'])); ?></td>
                                <td><a href="detail.php?id=<?php echo $job['lowongan_id']; ?>" class="btn-detail">Lihat Detail</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                 <p class="info-message">Anda belum melamar pekerjaan apapun.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
      <p>&copy; <?php echo date("Y"); ?> Cari Kerja.com</p>
      <p class="creators">
        Created by:
        <a href="#" target="_blank">Yehezkiel Darren/71231023</a> |
        <a href="#" target="_blank">Phillip Derric Kho/71231002</a> |
        <a href="#" target="_blank">Syendhi Reswara/71231061</a>
      </p>
    </footer>
    <script src="script/time.js"></script>
</body>
</html>