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
            lowongan.tanggal_buat,
            perusahaan.nama_perusahaan,
            perusahaan.logo
        FROM lowongan
        JOIN perusahaan ON lowongan.perusahaan_id = perusahaan.id
        where tanggal_batas > now()
        ORDER BY tanggal_buat DESC"; // Typo 'ORder' diperbaiki

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
            lowongan.tanggal_buat,
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
    $sql_filter .= " ORDER BY lowongan.tanggal_buat DESC"; // Tambahkan ORDER BY untuk konsistensi

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
    <script src="assets/js/search-filter.js"></script>
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
          <li><a href="about.php">About</a></li>
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
              echo '<img src="img/ProfilePicture.jpg" alt="profilepict" class="profilepicture" />'; // Default
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
        // Ambil 5 pekerjaan pertama untuk pengumuman
        $announcementJobs = array_slice($jobListFilter, 0, 5);
        foreach ($announcementJobs as $job): ?>
            
            <a href="detail.php?id=<?= $job['lowongan_id'] ?>" class="box-pengumuman-link">
                <div class="box-pengumuman">
                    <img 
                        src="<?= (isset($job['logo']) && !empty($job['logo']) && file_exists($job['logo'])) ? htmlspecialchars($job['logo']) : 'img/ProfilePicture.jpg' ?>" 
                        alt="Logo <?= htmlspecialchars($job['nama_perusahaan']) ?>" 
                        class="pengumuman-logo">
                    
                    <div class="pengumuman-details">
                        <h3 class="pengumuman-title"><?= htmlspecialchars($job['nama_pekerjaan']) ?></h3>
                        <p class="pengumuman-company"><?= htmlspecialchars($job['nama_perusahaan']) ?></p>
                        
                        <div class="pengumuman-meta">
                            <span class="meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <?= htmlspecialchars($job['lokasi']) ?>
                            </span>
                            <span class="meta-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                <?= htmlspecialchars($job['jenis_pekerjaan']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
      </section>
      <section class="job-container">
        <?php
            $jobCounter=0;
            foreach ($jobListFilter as $job): 
              $jobCounter++;
              $hiddenClass = ($jobCounter > 5) ? 'job-box-hidden' : '';
              // mencari banyaknya pekerja yang melamar
              $sql_count = "SELECT COUNT(*) as total FROM lamaran WHERE lowongan_id = " . intval($job['lowongan_id']);
              $result_count = mysqli_query($conn, $sql_count);
              $total_lamaran = 0;
              if ($result_count && mysqli_num_rows($result_count) > 0) {
                $row_count = mysqli_fetch_assoc($result_count);
                $total_lamaran = $row_count['total'];
              }
            ?>
          <div class="job-box <?= $hiddenClass ?>">
            <?php
              try{
                // Logika untuk menampilkan label "Baru" jika lowongan dibuat dalam 7 hari terakhir
                $tanggal_buat = new DateTime($job['tanggal_buat']);
                $tanggal_sekarang = new DateTime();
                $interval = $tanggal_buat->diff($tanggal_sekarang)->days;
                if ($interval <= 7) {
                  echo '<span class="new-job-label">Baru</span>';
                }
              }catch (Exception $e) {
                echo '<span class="error-label">Error</span>'; // Menangani error jika ada masalah dengan tanggal
              }
            ?>
            <div class="job-count">
              <?php if ($total_lamaran>0): ?>
                <p class="job-count-text">Total Pelamar: <?= htmlspecialchars($total_lamaran) ?></p>
              <?php else: ?>
                <p class="job-count-text">Belum ada pelamar</p>
              <?php endif; ?>
            </div>
            <img
              src="<?= (isset($job['logo']) && !empty($job['logo']) && file_exists($job['logo'])) ? htmlspecialchars($job['logo']) : 'img/ProfilePicture.jpg' ?>"
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
            <span>Lihat Detail</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>
          </div>
        <?php endforeach; ?>
        <div class="load-more-container">
            <button id="loadMoreBtn" class="btn-load-more">Tampilkan Lebih Banyak</button>
        </div>
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
    <script src="assets/js/search-filter.js"></script>
    <script src="assets/js/load-more.js"></script> 
  </body>
</html>