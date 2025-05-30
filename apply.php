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
  $id_pelamar = $_SESSION['id'] ?? "";
  // Cek apakah sudah login
  if (!isset($_SESSION['username']) || ! isset($_SESSION['role'])) {
      header("Location: login.php");
      exit();
  }
  // cek apakah user sudah pernah melamar

  $sql = "SELECT * FROM lamaran WHERE lowongan_id = ? AND pelamar_id = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ii", $id, $id_pelamar);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  if (mysqli_num_rows($result) > 0) {
    header("Location: index.php?apply_status=gagal");
    exit();
  }

  // Cek apakah role adalah pencari kerja
  if ($_SESSION['role'] !== 'pencari_kerja') {
      header("Location: dashboard-company.php");
      exit();
  }
  
  // cek apakah sudah apply
  $sql_cek_apply = "SELECT * FROM lamaran WHERE lowongan_id = ? AND nama_lengkap = ?";
  $stmt_cek_apply = mysqli_prepare($conn, $sql_cek_apply);
  mysqli_stmt_bind_param($stmt_cek_apply, "is", $id, $nama);
  mysqli_stmt_execute($stmt_cek_apply);
  $result_cek_apply = mysqli_stmt_get_result($stmt_cek_apply);
  if (mysqli_num_rows($result_cek_apply) > 0) {
    header("Location: index.php?apply_status=gagal");
    exit();
  } 

  // innput data apply ke database
  if (isset($_POST['submit'])) {
    $raw_nama_lengkap = $_POST['nama_lengkap'] ?? 'pelamar';
    $safe_nama_pelamar = str_replace(' ', '_', $raw_nama_lengkap);
    $safe_nama_pelamar = preg_replace('/[^A-Za-z0-9_]/', '', $safe_nama_pelamar);
    if (empty($safe_nama_pelamar)) {
        $safe_nama_pelamar = 'pelamar'; // Fallback jika nama menjadi kosong setelah sanitasi
    }

    $nama_lengkap_db = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $tanggal_lahir_db = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $email_db = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp_db = mysqli_real_escape_string($conn, $_POST['nomor_hp']);
    // $tanggal_lamaran = getTanggalSekarang(); // Variabel ini tidak digunakan di query INSERT

    // Simpan file CV (wajib)
    $cvResult = ['status' => false, 'message' => 'CV wajib diunggah.', 'path' => null];
    if (!empty($_FILES['cv']['name']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $cvExtension = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
        $cvFilename = "cv_" . $safe_nama_pelamar . "." . $cvExtension;
        $cvResult = simpanFile(
          $_FILES['cv'],
          5000000,
          $cvFilename,
          ['pdf', 'docx'],
          ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
      );
        if (!$cvResult['status']) $pesan_cv = $cvResult['message'];
    } elseif (!empty($_FILES['cv']['name']) && $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
        $pesan_cv = $cvResult['message'] ?? getUploadErrorMessage($_FILES['cv']['error']);
    } else { // Jika nama file kosong atau ada error UPLOAD_ERR_NO_FILE (meskipun CV required)
        $pesan_cv = "CV wajib diunggah dan tidak boleh kosong.";
    }

    // Simpan file portofolio jika ada (opsional)
    $portofolioPath = null;
    $portoResult = ['status' => true]; // Anggap sukses jika opsional dan tidak diunggah
    if (!empty($_FILES['portofolio']['name'])) {
        if ($_FILES['portofolio']['error'] === UPLOAD_ERR_OK) {
            $portoExtension = strtolower(pathinfo($_FILES['portofolio']['name'], PATHINFO_EXTENSION));
            $portoFilename = "portofolio_" . $safe_nama_pelamar . "." . $portoExtension;
            $portoResult = simpanFile(
              $_FILES['portofolio'],
              5000000,
              $portoFilename,
              ['pdf'],
              ['application/pdf']
          );
            if (!$portoResult['status']) $pesan_portofolio = $portoResult['message'];
            else $portofolioPath = $portoResult['path'];
        } else {
            $pesan_portofolio = $portoResult['message'] ?? getUploadErrorMessage($_FILES['portofolio']['error']);
            $portoResult['status'] = false;
        }
    }

    // Simpan file surat lamaran jika ada (opsional)
    $suratPath = null;
    $suratResult = ['status' => true]; // Anggap sukses jika opsional dan tidak diunggah
    if (!empty($_FILES['surat_lamaran']['name'])) {
        if ($_FILES['surat_lamaran']['error'] === UPLOAD_ERR_OK) {
            $suratExtension = strtolower(pathinfo($_FILES['surat_lamaran']['name'], PATHINFO_EXTENSION));
            $suratFilename = "lamaran_" . $safe_nama_pelamar . "." . $suratExtension;
            $suratResult = simpanFile(
              $_FILES['surat_lamaran'],
              5000000,
              $suratFilename,
              ['pdf', 'docx'], // Izinkan PDF dan DOCX untuk surat lamaran
              ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
          );
            if (!$suratResult['status']) $pesan_surat_lamaran = $suratResult['message'];
            else $suratPath = $suratResult['path'];
        } else {
            $pesan_surat_lamaran = $suratResult['message'] ?? getUploadErrorMessage($_FILES['surat_lamaran']['error']);
            $suratResult['status'] = false;
        }
    }

    // Jika semua file yang diunggah (atau wajib) valid
    if ($cvResult['status'] && $portoResult['status'] && $suratResult['status']) {
        $query2 = "INSERT INTO lamaran (lowongan_id, pelamar_id, nama_lengkap, tanggal_lahir, email, no_hp, cv, portofolio, surat_lamaran)
                   VALUES (?,?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = mysqli_prepare($conn, $query2);
        mysqli_stmt_bind_param($stmt2, "iisssssss", $_POST['lowongan_id'],$_POST['pencari_kerja_id'], $nama_lengkap_db, $tanggal_lahir_db, $email_db, $no_hp_db, $cvResult['path'], $portofolioPath, $suratPath);
        
        if (mysqli_stmt_execute($stmt2)) {
            header("Location: index.php?apply_status=success"); // Menggunakan apply_status sesuai index.php
            exit();
        } else {
            $pesan = "Gagal menyimpan lamaran: " . mysqli_error($conn);
        }
    } else {
        // Pesan umum jika ada file yang gagal diunggah, pesan spesifik sudah diatur
        if (empty($pesan)) { // Hanya set pesan umum jika belum ada pesan error spesifik dari DB
             $pesan = "Gagal menyimpan file. Periksa kembali file yang diunggah dan pesan error di atas.";
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
    <link rel="stylesheet" href="style/footer.css" />
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
                  <input type="file" id="surat_lamaran" name="surat_lamaran" accept=".pdf,.docx">
                </div>
                <button type="submit" name="submit" value="submit" class="btn-apply">Kirim Lamaran</button>
            </form>
        </div>
      </section>
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

  function simpanFile($file, $maxSize,$desiredFilename, array $allowedExtensions, array $allowedMimeTypes) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
      return ['status' => false, 'message' => getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE), 'path' => null];
    }

    if ($file['size'] > $maxSize) {
        return ['status' => false, 'message' => 'Ukuran file terlalu besar.', 'path' => null];
    }

    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        return ['status' => false, 'message' => 'Jenis file tidak diizinkan. Hanya file dengan ekstensi: ' . implode(', ', $allowedExtensions) . ' yang diperbolehkan.', 'path' => null];
    }

    // Validasi MIME type
    $mimeType = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $mimeType = mime_content_type($file['tmp_name']);
    } else {
        return ['status' => false, 'message' => 'Tidak dapat memverifikasi tipe file (fungsi MIME tidak tersedia).', 'path' => null];
    }

    if (!in_array($mimeType, $allowedMimeTypes)) {
        return ['status' => false, 'message' => 'Tipe file (MIME) tidak valid (' . htmlspecialchars($mimeType) . '). Pastikan file tidak korup dan sesuai dengan ekstensinya.', 'path' => null];
    }

    $targetDir = 'uploads/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetPath = $targetDir . $desiredFilename; // mengubah nama file

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['status' => true, 'message' => 'File berhasil disimpan.', 'path' => $targetPath];
    } else {
        return ['status' => false, 'message' => 'Gagal menyimpan file.', 'path' => null];
    }
  }

    function getUploadErrorMessage($errorCode) {
    switch ($errorCode) {
      case UPLOAD_ERR_OK:
        return ''; // Tidak ada error
      case UPLOAD_ERR_INI_SIZE:
          return "Ukuran file melebihi batas unggah server (php.ini directive).";
      case UPLOAD_ERR_FORM_SIZE:
          return "Ukuran file melebihi batas yang ditentukan dalam form HTML.";
      case UPLOAD_ERR_PARTIAL:
          return "File hanya terunggah sebagian.";
      case UPLOAD_ERR_NO_FILE:
          return "Tidak ada file yang diunggah.";
      case UPLOAD_ERR_NO_TMP_DIR:
          return "Folder sementara untuk unggahan tidak ditemukan.";
      case UPLOAD_ERR_CANT_WRITE:
          return "Gagal menulis file ke disk.";
      case UPLOAD_ERR_EXTENSION:
          return "Unggahan file dihentikan oleh ekstensi PHP.";
      default:
          return "Terjadi error tidak diketahui saat unggah file.";
    }
  }
?>
