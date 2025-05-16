<?php
  session_start();
  require_once 'connection.php';

  if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'perusahaan') {
    header("Location: dashboard-company.php");
    exit();
  }
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
        ORder by tanggal_batas DESC";

  $result = mysqli_query($conn, $sql);

  $jobList = [];

  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $jobList[] = $row;
    }
  } else {
    $pesan = "Tidak ada lowongan yang tersedia.";
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
        WHERE tanggal_batas > NOW()";

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
    <link rel="stylesheet" href="style/index.css" />
    <link rel="stylesheet" href="style/time.css" />
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
    <script src="script/time.js"></script>
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
        <form action="index.php" method="get">
          <div class="input">
            <input
              type="text"
              class="search-input"
              name="search_kerja"
              placeholder="Judul Pekerjaan"
            />
            <button name="submit_judul" type="submit" class="search-button">Cari</button>
          </div>
        </form>
      </div>
      <div class="filter-container">
        <form action="index.php" method="get">
          <select name="job_type" id="job-type" class="custom-select">
            <option value="">Job Type</option>
            <?php
              $sql = "SELECT DISTINCT jenis_pekerjaan FROM lowongan";
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
              $sql = "SELECT DISTINCT kategori FROM lowongan";
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
              $sql = "SELECT DISTINCT lokasi FROM lowongan";
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
        <div class="breadcrumb-bar">
          <div class="breadcrumb-text">
            <?= generateBreadcrumb(); ?>
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
      <section id="announcement-title">
        <div class="announcement-title">
          <p>Pengumuman 📢</p>
        </div>
      </section>
      <section class="announcement-container">
        <?php 
            foreach ($jobListFilter as $job): ?>
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
    </main>
    <footer>
      <p>&copy 2025 Cari Kerja.com</p>
    </footer>
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
  function generateBreadcrumb() {
      $path = $_SERVER['PHP_SELF']; // contoh: /project/detail.php
      $pathParts = explode('/', trim($path, '/')); // hapus slash & pecah
      $breadcrumb = '<div class="breadcrumb"><p>';

      $link = '';
      foreach ($pathParts as $index => $part) {
          $link .= '/' . $part;
          $name = ($part === 'index.php') ? 'Home' : ucfirst(str_replace(['.php', '-', '_'], ['',' ', ' '], $part));
          // Jadikan hanya bagian terakhir sebagai teks tanpa link
          if ($index < count($pathParts) - 1) {
              $breadcrumb .= '<a href="' . $link . '">' . $name . '</a> / ';
          } else {
              $breadcrumb .= $name;
          }
      }
      $breadcrumb .= '</p></div>';
      return $breadcrumb;
  }
?>