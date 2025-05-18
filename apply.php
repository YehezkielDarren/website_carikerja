<?php
    session_start();
    require_once 'connection.php';
    $pesan = "";
    $id = $_GET['id'] ?? "";
    $nama = $_SESSION['username'] ?? "";
    $logo = $_SESSION['logo'] ?? "";
    if (empty($id)) {
        $pesan = "Pekerjaan tidak ditemukan!";
        header("Location: index.php?pesan=$pesan");
        exit();
    }
    // Cek apakah sudah login
    if (!isset($_SESSION['username']) || ! isset($_SESSION['role'])) {
        header("Location: login.php");
        exit();
    }
    // Cek apakah role adalah pencari kerja
    if ($_SESSION['role'] !== 'pencari_kerja') {
        header("Location: dashboard-company.php");
        exit();
    }
    // menampilkan data lowongan berdasarkan id
    $query = "SELECT lowongan.* , perusahaan.nama_perusahaan,
    perusahaan.logo FROM lowongan JOIN perusahaan ON lowongan.perusahaan_id = perusahaan.id WHERE lowongan.id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (!$result || mysqli_num_rows($result) == 0) {
        $pesan = "Pekerjaan tidak ditemukan!";
        header("Location: index.php?pesan=$pesan");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style/apply.css" />
    <link rel="stylesheet" href="style/time.css" />
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
    <script src="script/time.js"></script>
    <title>Detail - Cari Kerja.com</title>
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
    <main>
      <div class="clock-container">
        <div class="clock-time" id="clock">00:00:00</div>
        <div class="clock-date" id="date">Loading date...</div>
      </div>
      <section id="welcome">
        <div class="breadcrumb-bar">
          <div class="breadcrumb-text">
            <?= generateBreadcrumb(); ?>
          </div>
          <a href="detail.php" class="btn-back">Kembali</a>
        </div>
      </section>
      <section class="job-container">
        <?php 
            foreach ($result as $job): ?>
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
          </div>
        <?php endforeach; ?>
      </section>
      <section class="apply-section">
          <h2>Apply Pekerjaan</h2>
            <form action="apply.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="pencari_kerja_id" value="<?= htmlspecialchars($_SESSION['id']) ?>">
            <input type="hidden" name="lowongan_id" value="<?= htmlspecialchars($id) ?>">

            <div class="form-group">
            <label for="nama_lengkap">Nama Lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="form-group">
            <label for="tanggal_lahir">Tanggal Lahir</label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir" required>
            </div>
            <div class="form-group">
            <label for="nomor_hp">Nomor HP</label>
            <input type="number" id="nomor_hp" name="nomor_hp" required>
            </div>
            <div class="form-group">
            <label for="cv">CV (PDF/DOCX)</label>
            <input type="file" id="cv" name="cv" accept=".pdf,.docx" required>
            </div>
            <div class="form-group">
            <label for="portofolio">Portofolio (PDF)</label>
            <input type="file" id="portofolio" name="portofolio" accept=".pdf" required>
            </div>
            <div class="form-group">
            <label for="surat_lamaran">Surat Lamaran</label>
            <input type="file" id="surat_lamaran" name="surat_lamaran">
            </div>
            <button type="submit" class="btn-apply">Kirim Lamaran</button>
          </form>
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
