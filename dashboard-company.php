<?php
  session_start();
  require_once 'connection.php';
  if (!isset($_SESSION['username']) || $_SESSION['role'] != 'perusahaan') {
    header("Location: index.php");
    exit();
  }
  $user_id = $_SESSION['id'];
  $pesan = "";
  $pesan_kosong = "";
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
      where (tanggal_batas > now() and lowongan.perusahaan_id = '$user_id')
      ORder by tanggal_batas DESC";

  $result = mysqli_query($conn, $sql);

  $jobList = [];

  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $jobList[] = $row;
    }
  } else {
    $pesan_kosong = "Anda belum membuat lowongan kerja.";
  }
  $jobListFilter = [];
  if (isset($_GET['submit_judul']) || isset($_GET['submit_filter'])) {
    $job_type_ = mysqli_real_escape_string($conn, $_GET['job_type'] ?? '');
    $kategori_ = mysqli_real_escape_string($conn, $_GET['kategori'] ?? '');
    $lokasi_ = mysqli_real_escape_string($conn, $_GET['lokasi'] ?? '');
    $search_kerja_ = mysqli_real_escape_string($conn, $_GET['search_kerja'] ?? '');

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
        WHERE tanggal_batas > NOW() AND lowongan.perusahaan_id = '$user_id'";

    if (!empty($job_type_)) {
        $sql .= " AND lowongan.jenis_pekerjaan = '$job_type_'";
    }
    if (!empty($kategori_)) {
        $sql .= " AND lowongan.kategori = '$kategori_'";
    }
    if (!empty($lokasi_)) {
        $sql .= " AND lowongan.lokasi = '$lokasi_'";
    }
    if (!empty($search_kerja_)) {
        $sql .= " AND lowongan.nama_pekerjaan LIKE '%$search_kerja_%'";
    }

    $sql .= " ORDER BY tanggal_batas DESC";

    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $jobListFilter[] = $row;
        }
    } else {
        // Jika tidak ada hasil, tampilkan pesan
        $pesan = "Tidak ada lowongan yang sesuai dengan kriteria pencarian.";
    }
  }else {
    $jobListFilter = $jobList;
  }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style/dashboard-company.css" />
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
    <!-- <script src="script/search-filter.js" defer></script> Hapus defer jika ada masalah timing -->
    <title>Home - Cari Kerja.com</title>
  </head>
  <body>
    <header>
      <div class="logo">
        <img src="img/LogoHeader1.png" alt="logokerja" />
        <a>Cari Kerja. <span class="small">com</span></a>
      </div>
      <nav>
        <ul type="none">
          <li><a href="index.html">Home</a></li>
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
    <section class="search-filter">
      <div class="search-container">
        <form action="dashboard-company.php" method="get">
          <div class="input">
            <input
              type="text"
              class="search-input"
              id="search-job-title-input"
              name="search_kerja"
              placeholder="Judul Pekerjaan"
            />
            <button name="submit_judul" type="submit" class="search-button">Cari</button>
          </div>
          <div id="search-job-title-error" class="search-error-message" style="display: none;"></div>
        </form>
      </div>
      <div class="filter-container">
        <form action="dashboard-company.php" method="get">
          <select name="job_type" id="job-type" class="custom-select">
            <option value="">Job Type</option>
            <?php
              $sql = "SELECT DISTINCT jenis_pekerjaan FROM lowongan WHERE perusahaan_id = '$user_id'";
              $result = mysqli_query($conn, $sql);
              $job_type = [];
              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  $job_type[] = $row['jenis_pekerjaan'];
                }
              }
              foreach($job_type as $jt) :?>
                <option value="<?= htmlspecialchars($jt) ?>"><?= htmlspecialchars($jt) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="kategori" id="kategori" class="custom-select">
            <option value="">Kategori</option>
            <?php 
              $sql = "SELECT DISTINCT kategori FROM lowongan WHERE perusahaan_id = '$user_id'"; 
              $result = mysqli_query($conn, $sql);
              $kategori = [];
              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  $kategori[] = $row['kategori'];
                }
              }
              foreach($kategori as $k) :?>
              <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
            <?php endforeach; ?>
          </select>
          <select name="lokasi" id="lokasi" class="custom-select">
            <option value="">Lokasi</option>
            <?php 
              $sql = "SELECT DISTINCT lokasi FROM lowongan WHERE perusahaan_id = '$user_id'";
              $result = mysqli_query($conn, $sql);
              $lokasi = [];
              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                  $lokasi[] = $row['lokasi'];
                }
              }
              foreach($lokasi as $l) :?>
              <option value="<?= htmlspecialchars($l) ?>"><?= htmlspecialchars($l) ?></option>
            <?php endforeach; ?>
          </select>
          <button name="submit_filter" type="submit" class="search-button">Cari</button>
        </form>
      </div>
    </section>
    <main>
      <section id="welcome">
        <div class="breadcrumb">
          <p><a href="index.php">Home</a></p>
        </div>
        <?php
          if (isset($_SESSION['username'])) {
            echo '<div class="welcome">Dashboard, <span class="username">' . htmlspecialchars($_SESSION['username']) . '</span></div>';
          }
        ?>
      </section>
      <div class="dashboard-layout">
        <div class="job-listings-column">
          <?php if (!empty($pesan_kosong) && !(isset($_GET['submit_judul']) || isset($_GET['submit_filter']))): ?>
              <section class="alert alert-info">
                  <p><?= htmlspecialchars($pesan_kosong); ?></p>
              </section>
          <?php elseif (!empty($pesan) && (isset($_GET['submit_judul']) || isset($_GET['submit_filter']))): ?>
              <section class="alert alert-warning">
                  <p><?= htmlspecialchars($pesan); ?></p>
              </section>
          <?php elseif (!empty($jobListFilter)): ?>
              <section class="job-container">
              <?php foreach ($jobListFilter as $job): ?>
                  <div class="job-box">
                    <img
                      src="<?= htmlspecialchars($job['logo']) ?>"
                      class="job-image"
                      alt="<?= htmlspecialchars($job['nama_perusahaan']) ?>"
                    />
                    <div class="job-content">
                      <h2 class="job-title"><?= htmlspecialchars($job['nama_pekerjaan']) ?></h2>
                      <h2 class="job-company"><?= htmlspecialchars($job['nama_perusahaan']) ?></h2>
                      <p class="job-location">📍 <?= htmlspecialchars($job['lokasi']) ?></p>
                      <p class="job-desc"><?= htmlspecialchars(potongDeskripsi($job['deskripsi'],20)) ?></p>
                      <p class="job-salary">💰 <?= htmlspecialchars($job['gaji']) ?></p>
                      <p class="job-date">
                        <span class="tanggal">Tanggal Batas: <?= htmlspecialchars(formatTanggal($job['tanggal_batas'])) ?></span>
                      </p>
                      <div class="status">
                        <p class="job-status <?= strtolower($job['jenis_pekerjaan']) ?>"><?= $job['jenis_pekerjaan'] ?></p>
                        <p class="job-status <?= strtolower($job['kategori']) ?>"><?= $job['kategori'] ?></p>
                      </div>
                    </div>
                    <div class="action-buttons">
                      <a href="cek-pelamar.php?id=<?= $job['lowongan_id'] ?>" class="btn-action lihat">👀 Pelamar</a>
                      <a href="edit-lowongan.php?id=<?= $job['lowongan_id'] ?>" class="btn-action edit">✏️ Edit</a>
                      <a href="hapus-lowongan.php?id=<?= $job['lowongan_id'] ?>" class="btn-action hapus" onclick="return confirm('Yakin ingin menghapus lowongan ini?')">🗑️ Hapus</a>
                    </div>
                  </div>
                <?php endforeach; ?>
              </section>
          <?php endif; ?>
        </div>

        <div class="add-job-column">
          <section class="add-job-form-container">
            <h2>Tambah Lowongan Pekerjaan Baru</h2>
            <form action="tambah-lowongan.php" method="POST">
              <div class="form-group">
                <label for="nama_pekerjaan_form">Nama Pekerjaan:</label>
                <input type="text" id="nama_pekerjaan_form" name="nama_pekerjaan" required>
              </div>
              <div class="form-group">
                <label for="jenis_pekerjaan_form">Jenis Pekerjaan:</label>
                <select id="jenis_pekerjaan_form" name="jenis_pekerjaan" required>
                  <option value="">Pilih Jenis Pekerjaan</option>
                  <option value="Full-time">Full-time</option>
                  <option value="Part-time">Part-time</option>
                  <option value="Internship">Internship</option>
                  <option value="Contract">Contract</option>
                  <option value="Freelance">Freelance</option>
                </select>
              </div>
              <div class="form-group">
                <label for="kategori_form">Kategori Pekerjaan:</label>
                <input type="text" id="kategori_form" name="kategori" required placeholder="Contoh: IT, Marketing, Desain">
              </div>
              <div class="form-group">
                <label for="lokasi_form">Lokasi:</label>
                <input type="text" id="lokasi_form" name="lokasi" required>
              </div>
              <div class="form-group">
                <label for="gaji_form">Gaji:</label>
                <input type="text" id="gaji_form" name="gaji" placeholder="Contoh: Rp 5.000.000 atau Kompetitif" required>
              </div>
              <div class="form-group">
                <label for="deskripsi_form">Deskripsi Pekerjaan:</label>
                <textarea id="deskripsi_form" name="deskripsi" rows="4" required></textarea>
              </div>
              <div class="form-group">
                <label for="syarat_form">Syarat Pekerjaan:</label>
                <textarea id="syarat_form" name="syarat" rows="4" required placeholder="Pisahkan setiap syarat dengan titik koma (;)"></textarea>
              </div>
              <div class="form-group">
                <label for="tanggal_batas_form">Tanggal Batas Lamaran:</label>
                <input type="date" id="tanggal_batas_form" name="tanggal_batas" required>
              </div>
              <div class="form-group">
                <label for="isPorto_form">Perlu Portofolio?</label>
                <select id="isPorto_form" name="isPorto" required>
                  <option value="0">Tidak</option>
                  <option value="1">Ya</option>
                </select>
              </div>
              <button type="submit" name="submit_tambah_lowongan" class="btn-submit-lowongan">Tambah Lowongan</button>
            </form>
          </section>
        </div>
      </div>            
    </main>
    <footer>
      <p>&copy; 2025 Cari Kerja.com</p>
      <p class="creators">
        Created by:
        <a href="#" target="_blank">Yehezkiel Darren/71231023</a> |
        <a href="#" target="_blank">Phillip Derric Kho/71231002</a> |
        <a href="#" target="_blank">Syendhi Reswara/71231061</a>
      </p>
    </footer>
    <script src="script/search-filter.js"></script>
  </body>
</html>
<?php
  function formatTanggal($tanggal) {
    $date = DateTime::createFromFormat('Y-m-d', $tanggal);
    if ($date === false) {
        return $tanggal;
    }
    $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
    return strftime('%d %B %Y', $date->getTimestamp());
  }
  
  function potongDeskripsi($teks, $jumlahKata = 20) {
    $kata = explode(" ", strip_tags($teks));
    if (count($kata) > $jumlahKata) {
        $kata = array_slice($kata, 0, $jumlahKata);
        return implode(" ", $kata) . "...";
    } else {
        return $teks;
    }
  }
?>