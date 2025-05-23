<?php
  session_start();
  require_once 'connection.php';
  $pesan_cv = "";
  $pesan_portofolio = "";
  $pesan_surat_lamaran = "";
  $pesan = "";
  $id = $_GET['id'] ?? ""; // lowongan id 
  $nama = $_SESSION['username'] ?? ""; // username user
  $logo = $_SESSION['logo'] ?? "";
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
  
  // cek apakah sudah apply

  

  // innput data apply ke database
  if (isset($_POST['submit'])) {
    $cv = $_FILES['cv']['name'];
    $portofolio = $_FILES['portofolio']['name'];
    $surat_lamaran = $_FILES['surat_lamaran']['name'];
    // validasi ukuran file cv
    $uploadOk = true;
    if ($cv!=""){
      if ($_FILES['cv']['size']>5000000){
          $pesan_cv="Ukuran File cv terlalu besar";
          $uploadOk=false;
      }
    }
    // validasi ukuran file portofolio"
    if ($portofolio!=""){
      if ($_FILES['portofolio']['size']>5000000){
          $pesan_portofolio="Ukuran File portofolio terlalu besar";
          $uploadOk=false;
      }
    } 
    // validasi ukuran file surat lamaran
    if ($surat_lamaran!=""){
      if ($_FILES['surat_lamaran']['size']>5000000){
          $pesan_surat_lamaran="Ukuran File surat lamaran terlalu besar";
          $uploadOk=false;
      }
    }
    if ($uploadOk) {
      $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
      $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
      $email = mysqli_real_escape_string($conn, $_POST['email']);
      $no_hp = mysqli_real_escape_string($conn, $_POST['nomor_hp']);
      $tanggal_lamaran = getTanggalSekarang();
  
      // Simpan file CV
      $cvResult = simpanFile($_FILES['cv'], 5000000);
      if (!$cvResult['status']) $pesan_cv = $cvResult['message'];
  
      // Simpan file portofolio jika ada
      $portofolioPath = null;
      if (!empty($_FILES['portofolio']['name'])) {
          $portoResult = simpanFile($_FILES['portofolio'], 5000000);
          if (!$portoResult['status']) $pesan_portofolio = $portoResult['message'];
          else $portofolioPath = $portoResult['path'];
      }
  
      // Simpan file surat lamaran jika ada
      $suratPath = null;
      if (!empty($_FILES['surat_lamaran']['name'])) {
          $suratResult = simpanFile($_FILES['surat_lamaran'], 5000000);
          if (!$suratResult['status']) $pesan_surat_lamaran = $suratResult['message'];
          else $suratPath = $suratResult['path'];
      }
  
      // Jika semua file valid
      if ($cvResult['status'] && ($portoResult['status'] ?? true) && ($suratResult['status'] ?? true)) {
          $query2 = "INSERT INTO lamaran (lowongan_id, nama_lengkap, tanggal_lahir, email, no_hp, cv, portofolio, surat_lamaran)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
          $stmt2 = mysqli_prepare($conn, $query2);
          mysqli_stmt_bind_param($stmt2, "isssssss", $_POST['lowongan_id'], $nama_lengkap, $tanggal_lahir, $email, $no_hp, $cvResult['path'], $portofolioPath, $suratPath);
          
          if (mysqli_stmt_execute($stmt2)) {
              $_SESSION['apply'] = "Apply Lamaran Berhasil Dilakukan";
              header("Location: index.php");
              exit();
          } else {
              $pesan = "Gagal menyimpan lamaran: " . mysqli_error($conn);
          }
      } else {
          $pesan = "Gagal menyimpan file. Periksa kembali file yang diunggah.";
        }
      }
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
  $hasil = mysqli_fetch_assoc($result);
  $syarat = explode("; ", $hasil['syarat']);
  // email autofill
  $query1="SELECT * from pencari_kerja WHERE id=?";
  $stmt1=mysqli_prepare($conn,$query1);
  mysqli_stmt_bind_param($stmt1,"s",$_SESSION['id']);
  mysqli_stmt_execute($stmt1);
  $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt1));
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
    <title>Apply - Cari Kerja.com</title>
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
      <div class="alert alert-danger" style="display: <?= empty($pesan) ? 'none' : 'block'; ?>;">
        <?= htmlspecialchars($pesan); ?>
      </div>
      <div class="alert alert-danger" style="display: <?= empty($pesan_cv) ? 'none' : 'block'; ?>;">
        <?= htmlspecialchars($pesan_cv); ?>
      </div>
      <div class="alert alert-danger" style="display: <?= empty($pesan_portofolio) ? 'none' : 'block'; ?>;">
        <?= htmlspecialchars($pesan_portofolio); ?>
      </div>
      <div class="alert alert-danger" style="display: <?= empty($pesan_surat_lamaran) ? 'none' : 'block'; ?>;">
        <?= htmlspecialchars($pesan_surat_lamaran); ?>
      </div>
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
      <section class="main-container">
        <div class="job-container">
          <div class="company-logo">
            <img src="<?php echo htmlspecialchars($hasil['logo']); ?>" alt="Logo Perusahaan">
          </div>
          <div class="job-details">
            <h1><?php echo htmlspecialchars($hasil['nama_pekerjaan']); ?></h1>
            <p class="company-name"><?php echo htmlspecialchars($hasil["nama_perusahaan"]); ?></p>
            <p class="location">📍<?php echo htmlspecialchars($hasil['lokasi']); ?></p>
            <p class="salary">💰<?php echo htmlspecialchars($hasil['gaji']); ?></p>
  
            <div class="description">
              <h2>Deskripsi Pekerjaan</h2>
              <p><?php echo nl2br(htmlspecialchars($hasil['deskripsi'])); ?></p>
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
        </div>
        <div class="apply-section">
            <h2>Apply Pekerjaan</h2>
              <form action="apply.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="pencari_kerja_id" value="<?= htmlspecialchars($_SESSION['id']) ?>">
                <input type="hidden" name="lowongan_id" value="<?= htmlspecialchars($id) ?>">
                <div class="form-group">
                  <label for="nama_lengkap">Nama Lengkap</label>
                  <input type="text" id="nama_lengkap" name="nama_lengkap" required value=" <?= isset($data['nama_lengkap']) ? htmlspecialchars($data['nama_lengkap']): '' ?> ">
                </div>
                <div class="form-group">
                  <label for="tanggal_lahir">Tanggal Lahir</label>
                  <input type="date" id="tanggal_lahir" name="tanggal_lahir" required value="<?= isset($data['tanggal_lahir']) ? htmlspecialchars($data['tanggal_lahir']) : '' ?>">
                </div>
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" id="email" name="email" value="<?= isset($data['email']) ? htmlspecialchars($data['email']): '' ?>">
                </div>
                <div class="form-group">
                  <label for="nomor_hp">Nomor HP</label>
                  <input type="number" id="nomor_hp" name="nomor_hp" value="<?= isset($data['no_hp']) ? htmlspecialchars($data['no_hp']): '' ?>" required>
                </div>
                <div class="form-group">
                  <label for="cv">CV (PDF/DOCX) max 5 MB</label>
                  <input type="file" id="cv" name="cv" accept=".pdf,.docx" required>
                </div>
                <?php if ($hasil['isPorto']==1): ?>
                <div class="form-group">
                  <label for="portofolio">Portofolio (PDF) max 5 MB</label>
                  <input type="file" id="portofolio" name="portofolio" accept=".pdf" >
                </div>
                <?php endif;?>
                <div class="form-group">
                  <label for="surat_lamaran">Surat Lamaran max 5 MB</label>
                  <input type="file" id="surat_lamaran" name="surat_lamaran">
                </div>
                <button type="submit" name="submit" value="submit" class="btn-apply">Kirim Lamaran</button>
            </form>
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
  
  function getTanggalSekarang() {
    date_default_timezone_set('Asia/Jakarta');
    return date('Y-m-d'); // contoh output: 2025-05-18
  }

  function simpanFile($file, $maxSize) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['status' => false, 'message' => 'Upload gagal.', 'path' => null];
    }

    if ($file['size'] > $maxSize) {
        return ['status' => false, 'message' => 'Ukuran file terlalu besar.', 'path' => null];
    }

    $targetDir = 'uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = uniqid() . '_' . basename($file['name']);
    $targetPath = $targetDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['status' => true, 'message' => 'File berhasil disimpan.', 'path' => $targetPath];
    } else {
        return ['status' => false, 'message' => 'Gagal menyimpan file.', 'path' => null];
    }
  }
?>
