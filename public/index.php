<?php
  session_start();
  // Path ke connection.php dan helpers.php disesuaikan
  require_once '../src/includes/connection.php'; // Updated path
  require_once '../src/includes/helpers.php';   // Updated path

  // ... (sisa kode PHP awal) ...
  // Redirect jika perusahaan
  if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'perusahaan') {
    header("Location: dashboard-company.php"); // Tetap karena dashboard-company.php ada di public/
    exit();
  }
  $pesan_gagal_apply = (isset($_GET['apply_status']) && $_GET['apply_status'] === 'gagal') ? 'Anda Sudah Melamar Lowongan Ini Sebelumnya!' : '';
  $apply_succes_message= (isset($_GET['apply_status']) && $_GET['apply_status'] === 'success') ? 'Apply Lamaran Berhasil Dilakukan!' : '';
  $pesan = "";
  $sql = "SELECT
            lowongan.id AS lowongan_id,
            lowongan.nama_pekerjaan,
            lowongan.kategori,
            lowongan.jenis_pekerjaan,
            lowongan.lokasi,
            lowongan.gaji,
            lowongan.deskripsi,
            lowongan.tanggal_batas,
            perusahaan.nama_perusahaan,
            perusahaan.logo
        FROM lowongan
        JOIN perusahaan ON lowongan.perusahaan_id = perusahaan.id
        where tanggal_batas > now()
        ORDER BY tanggal_batas DESC"; // Typo 'ORder' diperbaiki

  $result = mysqli_query($conn, $sql);
  $jobList = [];

  if ($result && mysqli_num_rows($result) > 0) { // Tambahkan pengecekan $result
    while ($row = mysqli_fetch_assoc($result)) {
      $jobList[] = $row;
    }
  } else {
    if (!$result) {
        $pesan = "Error query: " . mysqli_error($conn); // Pesan error jika query gagal
    } else {
        $pesan = "Tidak ada lowongan yang tersedia.";
    }
  }
  
  $jobListFilter = [];
  // Logika filter tetap sama, pastikan variabel $conn tersedia
  if (isset($_GET['submit_judul']) || isset($_GET['submit_filter'])) {
    $job_type_ = mysqli_real_escape_string($conn, $_GET['job_type'] ?? '');
    $kategori_ = mysqli_real_escape_string($conn, $_GET['kategori'] ?? '');
    $lokasi_ = mysqli_real_escape_string($conn, $_GET['lokasi'] ?? '');
    $search_kerja_ = mysqli_real_escape_string($conn, $_GET['search_kerja'] ?? '');

    $sql_filter = "SELECT
            lowongan.id AS lowongan_id,
            lowongan.nama_pekerjaan,
            lowongan.kategori,
            lowongan.jenis_pekerjaan,
            lowongan.lokasi,
            lowongan.gaji,
            lowongan.deskripsi,
            lowongan.tanggal_batas,
            perusahaan.nama_perusahaan,
            perusahaan.logo
        FROM lowongan
        JOIN perusahaan ON lowongan.perusahaan_id = perusahaan.id
        WHERE tanggal_batas > NOW()";

    if (!empty($job_type_)) {
        $sql_filter .= " AND lowongan.jenis_pekerjaan = '$job_type_'";
    }
    if (!empty($kategori_)) {
        $sql_filter .= " AND lowongan.kategori = '$kategori_'";
    }
    if (!empty($lokasi_)) {
        $sql_filter .= " AND lowongan.lokasi = '$lokasi_'";
    }
    if (!empty($search_kerja_)) {
        $sql_filter .= " AND lowongan.nama_pekerjaan LIKE '%$search_kerja_%'";
    }

    $sql_filter .= " ORDER BY tanggal_batas DESC";

    $result_filter = mysqli_query($conn, $sql_filter);
    if ($result_filter && mysqli_num_rows($result_filter) > 0) {
        while ($row_filter = mysqli_fetch_assoc($result_filter)) {
            $jobListFilter[] = $row_filter;
        }
    } else {
        if (!$result_filter && mysqli_error($conn)) {
             $pesan = "Error query filter: " . mysqli_error($conn);
        } else {
             $pesan = "Tidak ada lowongan yang sesuai dengan kriteria pencarian.";
        }
    }
  } else {
    $jobListFilter = $jobList;
  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <link rel="stylesheet" href="assets/css/time.css" />
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
    <link rel="stylesheet" href="assets/css/global-styles.css" />
    <script src="assets/js/search-filter.js></script>
    <script src="assets/js/time.js"></script>
    <title>Home - Cari Kerja.com</title>
  </head>
  <body>
    <div class="clock-container">
      <div class="clock-time" id="clock">00:00:00</div>
      <div class="clock-date" id="date">Loading date...</div>
    </div>
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
              if (isset($_SESSION['username'])) {
                echo '<a href="logout.php">Logout</a>';
              } else {
                echo '<a href="login.php">Login</a>';
              }
            ?>
          </li>
        </ul>
        <a href="dashboard-worker.php">
          <?php
            if(isset($_SESSION['logo']) && !empty($_SESSION['logo']) && file_exists($_SESSION['logo'])) { // Cek file exists
              echo '<img src="' . htmlspecialchars($_SESSION['logo']) . '" alt="profilepict" class="profilepicture" />';
            } else {
              echo '<img src="assets/img/ProfilePicture.jpg" alt="profilepict" class="profilepicture" />'; // Default
            }
          ?>
        </a>
      </nav>
    </header>
    <main>
      <section class="search-filter">
        <div class="search-container">
          <form action="index.php" method="get">
            <div class="input">
              <input
                type="text"
                class="search-input"
                name="search_kerja"
                id="search-job-title-input"
                placeholder="Judul Pekerjaan"
                value="<?= htmlspecialchars($_GET['search_kerja'] ?? '') ?>"
              />
              <button name="submit_judul" type="submit" class="search-button">Cari</button>
            </div>
            <div id="search-job-title-error" class="search-error-message" style="display: none;"></div>
          </form>
        </div>
        <div class="filter-container">
          <form action="index.php" method="get">
             <select name="job_type" id="job-type" class="custom-select">
                <option value="">Job Type</option>
                <?php
                  $current_job_type = $_GET['job_type'] ?? '';
                  $sql_jt = "SELECT DISTINCT jenis_pekerjaan FROM lowongan WHERE tanggal_batas > NOW() ORDER BY jenis_pekerjaan";
                  $result_jt = mysqli_query($conn, $sql_jt);
                  if ($result_jt && mysqli_num_rows($result_jt) > 0) {
                    while ($row_jt = mysqli_fetch_assoc($result_jt)) {
                      $selected = ($row_jt['jenis_pekerjaan'] === $current_job_type) ? 'selected' : '';
                      echo '<option value="' . htmlspecialchars($row_jt['jenis_pekerjaan']) . '" ' . $selected . '>' . htmlspecialchars($row_jt['jenis_pekerjaan']) . '</option>';
                    }
                  }
                ?>
            </select>
            <select name="kategori" id="kategori" class="custom-select">
                <option value="">Kategori</option>
                <?php
                  $current_kategori = $_GET['kategori'] ?? '';
                  $sql_k = "SELECT DISTINCT kategori FROM lowongan WHERE tanggal_batas > NOW() ORDER BY kategori";
                  $result_k = mysqli_query($conn, $sql_k);
                  if ($result_k && mysqli_num_rows($result_k) > 0) {
                    while ($row_k = mysqli_fetch_assoc($result_k)) {
                      $selected = ($row_k['kategori'] === $current_kategori) ? 'selected' : '';
                      echo '<option value="' . htmlspecialchars($row_k['kategori']) . '" ' . $selected . '>' . htmlspecialchars($row_k['kategori']) . '</option>';
                    }
                  }
                ?>
            </select>
            <select name="lokasi" id="lokasi" class="custom-select">
                <option value="">Lokasi</option>
                <?php
                  $current_lokasi = $_GET['lokasi'] ?? '';
                  $sql_l = "SELECT DISTINCT lokasi FROM lowongan WHERE tanggal_batas > NOW() ORDER BY lokasi";
                  $result_l = mysqli_query($conn, $sql_l);
                  if ($result_l && mysqli_num_rows($result_l) > 0) {
                    while ($row_l = mysqli_fetch_assoc($result_l)) {
                      $selected = ($row_l['lokasi'] === $current_lokasi) ? 'selected' : '';
                      echo '<option value="' . htmlspecialchars($row_l['lokasi']) . '" ' . $selected . '>' . htmlspecialchars($row_l['lokasi']) . '</option>';
                    }
                  }
                ?>
            </select>
            <input type="hidden" name="search_kerja" value="<?= htmlspecialchars($_GET['search_kerja'] ?? '') ?>">
            <button name="submit_filter" type="submit" class="search-button">Filter</button>
          </form>
        </div>
      </section>
      <section id="welcome">
        <div class="breadcrumb-bar">
          <div class="breadcrumb-box">
            <div class="breadcrumb-text-inactive">
                  <a href="dashboard-worker.php">Dashboard</a>
            </div>  
            <span class="separator-breadcrumb">></span>
            <div class="breadcrumb-text">
                <a href="#">Home</a>
            </div>
          </div>
        </div>
        <?php
          if (isset($_SESSION['username'])) {
            echo '<div class="welcome">Welcome, <span class="username">' . htmlspecialchars($_SESSION['username']) . '</span></div>';
          }
        ?>
      </section>
      <section class="alert alert-danger" id="errorMsg" style="display: <?= empty($pesan) ? 'none' : 'block'; ?>;">
        <p><?= htmlspecialchars($pesan); ?></p>
      </section>
      <section class="alert alert-danger" id="errorMsgApplyFail" style="display: <?= empty($pesan_gagal_apply) ? 'none' : 'block'; ?>;">
        <p><?= htmlspecialchars($pesan_gagal_apply); ?></p>
      </section>
      <section class="alert alert-succes" id="successMsgApply" style="display: <?= empty($apply_succes_message) ? 'none' : 'block'; ?>;">
        <p><?= htmlspecialchars($apply_succes_message); ?></p>
      </section>
      
      <?php if (!empty($jobListFilter)): // Hanya tampilkan jika ada pekerjaan ?>
      <section id="announcement-title">
        <div class="announcement-title">
          <p>Lowongan Terbaru 📢</p>
        </div>
      </section>
      <section class="announcement-container">
        <?php
            $announcementJobs = array_slice($jobListFilter, 0, 5); // Ambil 5 pekerjaan pertama untuk pengumuman
            foreach ($announcementJobs as $job): ?>
              <div class="box-pengumuman">
                <h3><?= htmlspecialchars($job['nama_pekerjaan']) ?></h3>
                <h4><?= htmlspecialchars($job['nama_perusahaan']) ?></h4>
                <h5 class="status-kerja"><?= htmlspecialchars($job['jenis_pekerjaan']) ?></h5>
                <h6 class="gaji"><?= htmlspecialchars($job['gaji']) ?></h6>
              </div>
            <?php endforeach; ?>
      </section>

      <section class="job-container">
        <?php
            foreach ($jobListFilter as $job): ?>
          <div class="job-box">
            <img
              src="<?= (isset($job['logo']) && !empty($job['logo']) && file_exists($job['logo'])) ? htmlspecialchars($job['logo']) : 'assets/img/ProfilePicture.jpg' ?>"
              class="job-image"
              alt="<?= htmlspecialchars($job['nama_perusahaan']) ?>"
            />
            <div class="job-content">
              <h2 class="job-title"><?= htmlspecialchars($job['nama_pekerjaan']) ?></h2>
              <h2 class="job-company"><?= htmlspecialchars($job['nama_perusahaan']) ?></h2>
              <p class="job-location">📍 <?= htmlspecialchars($job['lokasi']) ?></p>
              <p class="job-desc"><?= htmlspecialchars(potongDeskripsi($job['deskripsi'],15)) // Fungsi dari helpers.php ?></p>
              <p class="job-salary">💰 <?= htmlspecialchars($job['gaji']) ?></p>
              <p class="job-date">
                <span class="tanggal">Tanggal Batas: <?= htmlspecialchars(formatTanggal($job['tanggal_batas'])) // Fungsi dari helpers.php ?></span>
              </p>
              <div class="status">
                <p class="job-status <?= strtolower(htmlspecialchars($job['jenis_pekerjaan'])) ?>"><?= htmlspecialchars($job['jenis_pekerjaan']) ?></p>
                <p class="job-status <?= strtolower(htmlspecialchars($job['kategori'])) ?>"><?= htmlspecialchars($job['kategori']) ?></p>
              </div>
            </div>
            <a href="detail.php?id=<?= $job['lowongan_id'] ?>" class="btn-detail">
              <img
                src="https://img.icons8.com/?size=100&id=85460&format=png&color=000000" 
                                alt="more"
              />
              Check for more info
            </a>
          </div>
        <?php endforeach; ?>
      </section>
      <?php endif; ?>
    </main>
    <footer>
      <p>&copy; <?= date("Y"); ?> Cari Kerja.com</p> <p class="creators">
        Created by:
        <a href="#" target="_blank">Yehezkiel Darren/71231023</a> |
        <a href="#" target="_blank">Phillip Derric Kho/71231002</a> |
        <a href="#" target="_blank">Syendhi Reswara/71231061</a>
      </p>
    </footer>
    <script src="assets/js/search-filter.js"></script> </body>
</html>