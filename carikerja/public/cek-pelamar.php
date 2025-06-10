<?php
    require '../src/includes/connection.php';
    require_once '../src/includes/helpers.php';
    // session_start();
    // validasi login
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])){
        header("location: login.php");
        exit();
    }
    if ($_SESSION['role']=="pencari_kerja"){
        header("location: index.php");
        exit();
    }
    $applicants = [];
    $data_lowongan = [];
    $judul_lowongan_terpilih = "";
    $id_lowongan_terpilih = "";

    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_lowongan_terpilih = $_GET['id'];
        // Ambil data lowongan dari database berdasarkan $id_lowongan_terpilih
        $sql_lowongan = "SELECT id, nama_pekerjaan FROM lowongan WHERE id = ?";
        $stmt_lowongan = mysqli_prepare($conn, $sql_lowongan);
        if ($stmt_lowongan){
            mysqli_stmt_bind_param($stmt_lowongan, "i", $id_lowongan_terpilih);
            mysqli_stmt_execute($stmt_lowongan);
            $result_lowongan = mysqli_stmt_get_result($stmt_lowongan);
            $lowongan = mysqli_fetch_assoc($result_lowongan);
            if ($lowongan){
                $data_lowongan=$lowongan;
                $judul_lowongan_terpilih=$data_lowongan['nama_pekerjaan'] . " (ID: " . $data_lowongan['id'] . ")";

                // ambil data pelamar dari databse
                $sql_applicants = "SELECT 
                                            l.id_lamaran,
                                            l.nama_lengkap,
                                            l.tanggal_lahir,
                                            l.no_hp,
                                            l.email,
                                            l.tanggal_lamaran,
                                            l.cv,
                                            l.portofolio,
                                            l.surat_lamaran,
                                            pk.foto AS logo_akun,
                                            u.username
                                    FROM lamaran l
                                    JOIN pencari_kerja pk ON l.pelamar_id = pk.id
                                    JOIN users u ON pk.user_id = u.id
                                    WHERE l.lowongan_id = ?";
                $stmt_applicants = mysqli_prepare($conn, $sql_applicants);
                if ($stmt_applicants){
                    mysqli_stmt_bind_param($stmt_applicants, "i", $id_lowongan_terpilih);
                    mysqli_stmt_execute($stmt_applicants);
                    $result_applicants = mysqli_stmt_get_result($stmt_applicants);
                    while ($applicant = mysqli_fetch_assoc($result_applicants)) {
                        $applicants[] = $applicant;
                    }
                    mysqli_stmt_close($stmt_applicants);
                }else{
                    error_log("MySQLi prepare error for applicants: " . mysqli_error($conn));
                }
            }
            mysqli_stmt_close($stmt_lowongan);
        }else{
            error_log("MySQLi prepare error for lowongan: " . mysqli_error($conn));
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/cek-pelamar.css">
    <link rel="stylesheet" href="assets/css/global-styles.css">
    <link rel="stylesheet" href="assets/css/time.css">
    <link rel="icon" type="image/png" href="img/LogoHeader1.png">
    <script src="assets/js/detial-pelamar.js"></script>
    <script src="assets/js/time.js"></script>
    <title>List Pelamar</title>
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
          <li>
            <?php
              // Check if the user is logged in
              if (isset($_SESSION['username'])) {
                echo '<a href="logout.php">Logout</a>';
              } else {
                echo '<a href="login.php">Login</a>';
              }
            ?>
          </li>
        </ul>
        <a href="login.php">
          <?php
            if(isset($_SESSION['logo'])) {
              echo '<img src="' . htmlspecialchars($_SESSION['logo']) . '" alt="profilepict" class="profilepicture" />';
            } else {
              echo '<img src="img/ProfilePicture.jpg" alt="profilepict" class="profilepicture" />';
            }
          ?>
        </a>
      </nav>
    </header>   
    <div class="breadcrumb-bar">
        <div class="breadcrumb-box"> 
            <div class="breadcrumb-text-inactive">
                <a href="index.php">Dashboard</a>
            </div>
            <span class="breadcrumb-separator">></span>
            <div class="breadcrumb-text">
                <a href="#">Cek-Pelamar</a>
            </div>
        </div>
        <a href="dashboard-company.php" class="btn-back">Kembali</a>
    </div> 
    <main class="main-content">
        <div class="applicant-details-container" id="applicantDetailsContainer">
            <div class="details-placeholder" id="detailsPlaceholder">
                <i class="fas fa-info-circle"></i>
                <p>Klik tombol "Lihat Detail" pada tabel di samping untuk menampilkan rincian pelamar.</p>
            </div>
            <div class="details-view" id="detailsView" style="display: none;">
                <img id="detail-logo" src="img/ProfilePicture.jpg" alt="Logo Pelamar" class="detail-applicant-logo">
                <h3 id="detail-nama-lengkap">Nama Lengkap Pelamar</h3>
                <p><strong>Username:</strong> <span id="detail-username"></span></p>
                <p><strong>Email:</strong> <span id="detail-email"></span></p>
                <p><strong>No. HP:</strong> <span id="detail-nomor-telepon"></span></p>
                <p><strong>Tanggal Lahir:</strong> <span id="detail-tanggal-lahir"></span></p>
                
                <h4>Dokumen Terlampir:</h4>
                <ul class="document-list">
                    <li>
                        CV: <a id="detail-cv-link" href="#" target="_blank" download>Unduh CV</a>
                        <span class="no-file-text" id="cv-no-file">(Tidak tersedia)</span>
                    </li>
                    <li>
                        Portofolio: <a id="detail-portofolio-link" href="#" target="_blank" download>Unduh Portofolio</a>
                        <span class="no-file-text" id="portofolio-no-file">(Tidak tersedia)</span>
                    </li>
                    <li>
                        Surat Lamaran: <a id="detail-surat-lamaran-link" href="#" target="_blank" download>Unduh Surat Lamaran</a>
                        <span class="no-file-text" id="surat-lamaran-no-file">(Tidak tersedia)</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="applicant-list-container">
            <h2>Daftar Pelamar untuk Lowongan: <?= htmlspecialchars($judul_lowongan_terpilih) ?></h2>
            <?php if (empty($applicants)): ?>
                <div class="no-applicants-placeholder">
                    <div class="placeholder-icon-container">
                        <i class="fas fa-user-slash placeholder-icon"></i>
                    </div>
                    <p class="placeholder-text">Belum ada pelamar di lowongan ini.</p>
                </div>
            <?php else: ?>
                <table class="applicant-table">
                    <thead>
                        <tr>
                            <th>ID Lamaran</th>
                            <th>Nama Lengkap</th>
                            <th>Tanggal Lahir</th>
                            <th>No. Telepon</th>
                            <th>Email</th>
                            <th>Tanggal Lamar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applicants as $applicant): ?>
                            <tr>
                                <td><?= htmlspecialchars($applicant['id_lamaran']) ?></td>
                                <td><?= htmlspecialchars($applicant['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($applicant['tanggal_lahir']))) ?></td>
                                <td><?= htmlspecialchars($applicant['no_hp']) ?></td>
                                <td><?= htmlspecialchars($applicant['email']) ?></td>
                                <td><?= htmlspecialchars(date('d M Y', strtotime($applicant['tanggal_lamaran']))) ?></td>
                                <td>
                                    <button class="btn-detail"
                                        data-id_lamaran="<?= htmlspecialchars($applicant['id_lamaran']) ?>"
                                        data-nama_lengkap="<?= htmlspecialchars($applicant['nama_lengkap']) ?>"
                                        data-tanggal_lahir="<?= htmlspecialchars(date('Y-m-d', strtotime($applicant['tanggal_lahir']))) ?>"
                                        data-nomor_telepon="<?= htmlspecialchars($applicant['no_hp']) ?>"
                                        data-email="<?= htmlspecialchars($applicant['email']) ?>"
                                        data-logo_akun="<?= htmlspecialchars($applicant['logo_akun'] ?? 'img/ProfilePicture.jpg') ?>"
                                        data-username="<?= htmlspecialchars($applicant['username'] ?? 'N/A') ?>"
                                        data-cv_file="<?= htmlspecialchars($applicant['cv'] ?? '') ?>"
                                        data-portofolio_file="<?= htmlspecialchars($applicant['portofolio'] ?? '') ?>"
                                        data-surat_lamaran_file="<?= htmlspecialchars($applicant['surat_lamaran'] ?? '') ?>">
                                        Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> Cari Kerja.com</p>
        <p class="creators">
            Created by:
            <a href="#" target="_blank">Yehezkiel Darren/71231023</a> |
            <a href="#" target="_blank">Phillip Derric Kho/71231002</a> |
            <a href="#" target="_blank">Syendhi Reswara/71231061</a>
        </p>
    </footer>
</body>
</html>
