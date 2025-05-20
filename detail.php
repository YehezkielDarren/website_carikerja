<?php
    session_start();
    require_once 'connection.php';
    $pesan = "";
    $id = $_GET['id'] ?? ""; // id lowongan pekerjaan
    $nama = $_SESSION['username'] ?? "";
    $logo = $_SESSION['logo'] ?? "";
    if (empty($id)) {
        $pesan = "Pekerjaan tidak ditemukan!";
        header("Location: index.php?pesan=$pesan");
        exit();
    }
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
    $data = mysqli_fetch_assoc($result);
    $syarat = explode("; ", $data['syarat']);
    // cek apakah user sudah pernah melamar
    $lamar = false;
    $query = "SELECT * FROM lamaran WHERE pencari_kerja_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $lamar = true;
    }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style/detail.css" />
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
          <a href="index.php" class="btn-back">Kembali</a>
        </div>
      </section>
      <section class="job-container" style="justify-content: center;">
        <div class="job-box">
          <div class="logo-container">
            <img src="<?php echo htmlspecialchars($data['logo']); ?>" alt="Logo Perusahaan">
          </div>
          <div class="job-details">
            <h1><?php echo htmlspecialchars($data['nama_pekerjaan']); ?></h1>
            <p class="company-name"><?php echo htmlspecialchars($data["nama_perusahaan"]); ?></p>
            <p class="location">📍<?php echo htmlspecialchars($data['lokasi']); ?></p>
            <p class="salary">💰<?php echo htmlspecialchars($data['gaji']); ?></p>
  
            <div class="description">
              <h2>Deskripsi Pekerjaan</h2>
              <p><?php echo nl2br(htmlspecialchars($data['deskripsi'])); ?></p>
            </div>
            <div class="requirements">
              <h4>Syarat Pekerjaan:</h4>
              <ul>
                <?php foreach ($syarat as $item): ?>
                  <li><?php echo htmlspecialchars(trim($item)); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
          <div>            
            <?php if ($lamar==false){?>
              <a href="apply.php?id=<?php echo htmlspecialchars($id); ?>" class="btn-apply">Lamar Sekarang</a>
            <?php }else{?>
          </div>
          <div>
            <a href="#" class="btn-alrapply">Anda Sudah Melamar</a>
          </div>
          <?php }?>
        </div>
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
