<?php
  session_start();
  require_once '../src/includes/connection.php'; // Updated
  require_once '../src/includes/helpers.php';   // Updated

  $errors = []; // Array untuk menampung semua pesan error
  $pesan_display = ""; // Variabel untuk menampilkan pesan error di HTML
  $id = $_GET['id'] ?? ""; // lowongan id
  $nama_session_user = $_SESSION['username'] ?? "";
  $id_pelamar = $_SESSION['id'] ?? ""; 
  // Cek apakah sudah login
  if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
      header("Location: login.php");
      exit();
  }

  // Cek apakah role adalah pencari kerja
  if ($_SESSION['role'] !== 'pencari_kerja') {
      // Jika bukan pencari kerja, mungkin redirect ke dashboard yang sesuai atau index
      if ($_SESSION['role'] === 'perusahaan') {
          header("Location: dashboard-company.php");
      } else {
          header("Location: index.php");
      }
      exit();
  }
  
  // Cek apakah user sudah pernah melamar lowongan ini
  // Menggunakan id_pelamar (INT) dan id (INT)
  if (!empty($id) && !empty($id_pelamar)) {
      $sql_check_applied = "SELECT * FROM lamaran WHERE lowongan_id = ? AND pelamar_id = ?";
      $stmt_check_applied = mysqli_prepare($conn, $sql_check_applied);
      if ($stmt_check_applied) {
          mysqli_stmt_bind_param($stmt_check_applied, "ii", $id, $id_pelamar);
          mysqli_stmt_execute($stmt_check_applied);
          $result_check_applied = mysqli_stmt_get_result($stmt_check_applied);
          if (mysqli_num_rows($result_check_applied) > 0) {
              header("Location: index.php?apply_status=gagal");
              exit();
          }
          mysqli_stmt_close($stmt_check_applied);
      } else {
          $errors[] = "Gagal memeriksa status lamaran: " . mysqli_error($conn);
      }
  }


  // Input data apply ke database
  if (isset($_POST['submit'])) {
    // Validasi bahwa lowongan_id dan pencari_kerja_id ada dan numerik
    $lowongan_id_post = filter_input(INPUT_POST, 'lowongan_id', FILTER_VALIDATE_INT);
    $pencari_kerja_id_post = filter_input(INPUT_POST, 'pencari_kerja_id', FILTER_VALIDATE_INT);

    if (!$lowongan_id_post || !$pencari_kerja_id_post) {
        $errors[] = "ID Lowongan atau ID Pelamar tidak valid.";
    } else if ($pencari_kerja_id_post != $id_pelamar) {
        // Keamanan tambahan: pastikan ID pelamar dari form sama dengan dari session
        $errors[] = "Kesalahan identitas pelamar.";
    } else {
        $raw_nama_lengkap = $_POST['nama_lengkap'] ?? 'pelamar';
        $safe_nama_pelamar = str_replace(' ', '_', $raw_nama_lengkap);
        $safe_nama_pelamar = preg_replace('/[^A-Za-z0-9_]/', '', $safe_nama_pelamar);
        if (empty($safe_nama_pelamar)) {
            $safe_nama_pelamar = 'pelamar';
        }

        $nama_lengkap_db = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
        $tanggal_lahir_db = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
        $email_db = mysqli_real_escape_string($conn, trim($_POST['email']));
        $no_hp_db = mysqli_real_escape_string($conn, trim($_POST['nomor_hp']));

        $cv_file_info = null;
        $portofolio_file_info = null;
        $surat_lamaran_file_info = null;
        $upload_base_dir = 'uploads/';

        // Validasi file CV (wajib)
        if (!empty($_FILES['cv']['name']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
            $cvExtension = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
            $cvFilename = "cv_" . $safe_nama_pelamar . "_" . time() . "." . $cvExtension;
            $cvResult = simpanFile(
                $_FILES['cv'],
                5000000, // 5MB
                $cvFilename,
                ['pdf', 'docx'],
                ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
            );
            if (!$cvResult['status']) {
                $errors[] = "CV: " . $cvResult['message'];
            } else {
                $cv_file_info = [
                    'tmp_name' => $cvResult['tmp_name'],
                    'db_path' => $upload_base_dir . $cvResult['validated_filename'],
                    'target_move_path' => $upload_base_dir . $cvResult['validated_filename']
                ];
            }
        } elseif (!empty($_FILES['cv']['name']) && $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "CV: " . getUploadErrorMessage($_FILES['cv']['error']);
        } else {
            $errors[] = "CV wajib diunggah dan tidak boleh kosong.";
        }

        // Validasi file portofolio jika ada (opsional)
        if (!empty($_FILES['portofolio']['name'])) {
            if ($_FILES['portofolio']['error'] === UPLOAD_ERR_OK) {
                $portoExtension = strtolower(pathinfo($_FILES['portofolio']['name'], PATHINFO_EXTENSION));
                $portoFilename = "portofolio_" . $safe_nama_pelamar . "_" . time() . "." . $portoExtension;
                $portoResult = simpanFile(
                    $_FILES['portofolio'],
                    5000000, // 5MB
                    $portoFilename,
                    ['pdf'],
                    ['application/pdf']
                );
                if (!$portoResult['status']) {
                    $errors[] = "Portofolio: " . $portoResult['message'];
                } else {
                    $portofolio_file_info = [
                        'tmp_name' => $portoResult['tmp_name'],
                        'db_path' => $upload_base_dir . $portoResult['validated_filename'],
                        'target_move_path' => $upload_base_dir . $portoResult['validated_filename']
                    ];
                }
            } else {
                $errors[] = "Portofolio: " . getUploadErrorMessage($_FILES['portofolio']['error']);
            }
        }

        // Validasi file surat lamaran jika ada (opsional)
        if (!empty($_FILES['surat_lamaran']['name'])) {
            if ($_FILES['surat_lamaran']['error'] === UPLOAD_ERR_OK) {
                $suratExtension = strtolower(pathinfo($_FILES['surat_lamaran']['name'], PATHINFO_EXTENSION));
                $suratFilename = "lamaran_" . $safe_nama_pelamar . "_" . time() . "." . $suratExtension;
                $suratResult = simpanFile(
                    $_FILES['surat_lamaran'],
                    5000000, // 5MB
                    $suratFilename,
                    ['pdf', 'docx'],
                    ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
                );
                if (!$suratResult['status']) {
                    $errors[] = "Surat Lamaran: " . $suratResult['message'];
                } else {
                    $surat_lamaran_file_info = [
                        'tmp_name' => $suratResult['tmp_name'],
                        'db_path' => $upload_base_dir . $suratResult['validated_filename'],
                        'target_move_path' => $upload_base_dir . $suratResult['validated_filename']
                    ];
                }
            } else {
                $errors[] = "Surat Lamaran: " . getUploadErrorMessage($_FILES['surat_lamaran']['error']);
            }
        }

        if (empty($errors)) { // Hanya proses jika tidak ada error validasi sebelumnya
            $cv_db_path_to_store = $cv_file_info['db_path'] ?? null;
            $portofolio_db_path_to_store = $portofolio_file_info['db_path'] ?? null;
            $surat_lamaran_db_path_to_store = $surat_lamaran_file_info['db_path'] ?? null;

            $query2 = "INSERT INTO lamaran (lowongan_id, pelamar_id, nama_lengkap, tanggal_lahir, email, no_hp, cv, portofolio, surat_lamaran, tanggal_lamaran)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt2 = mysqli_prepare($conn, $query2);
            mysqli_stmt_bind_param($stmt2, "iisssssss", $lowongan_id_post, $pencari_kerja_id_post, $nama_lengkap_db, $tanggal_lahir_db, $email_db, $no_hp_db, $cv_db_path_to_store, $portofolio_db_path_to_store, $surat_lamaran_db_path_to_store);
            
            if (mysqli_stmt_execute($stmt2)) {
                // Data berhasil masuk DB, sekarang pindahkan file
                $files_moved_successfully = true;
                if (!is_dir($upload_base_dir)) {
                    if (!mkdir($upload_base_dir, 0755, true)) {
                        $errors[] = "Gagal membuat direktori uploads: " . $upload_base_dir;
                        $files_moved_successfully = false;
                    }
                }

                if ($files_moved_successfully && $cv_file_info) {
                    if (!move_uploaded_file($cv_file_info['tmp_name'], $cv_file_info['target_move_path'])) {
                        $errors[] = "Gagal menyimpan file CV.";
                        $files_moved_successfully = false;
                    }
                }
                if ($files_moved_successfully && $portofolio_file_info) {
                    if (!move_uploaded_file($portofolio_file_info['tmp_name'], $portofolio_file_info['target_move_path'])) {
                        $errors[] = "Gagal menyimpan file Portofolio.";
                        $files_moved_successfully = false;
                    }
                }
                if ($files_moved_successfully && $surat_lamaran_file_info) {
                    if (!move_uploaded_file($surat_lamaran_file_info['tmp_name'], $surat_lamaran_file_info['target_move_path'])) {
                        $errors[] = "Gagal menyimpan file Surat Lamaran.";
                        $files_moved_successfully = false;
                    }
                }

                if ($files_moved_successfully) {
                    header("Location: index.php?apply_status=success");
                    exit();
                } else {
                    // DB insert OK, tapi pemindahan file gagal. $errors sudah berisi pesan error file.
                }
            } else {
                $errors[] = "Gagal menyimpan lamaran ke database: " . mysqli_error($conn);
            }
            if ($stmt2) mysqli_stmt_close($stmt2);
        }
    }
    // Jika ada error dari validasi awal atau proses di atas
    if (!empty($errors)) {
        $pesan_display = implode("<br>", array_map('htmlspecialchars', $errors));
    }
}
      
  
  // Menampilkan data lowongan berdasarkan id
  // Pastikan $id (dari GET) valid sebelum digunakan
  $data_lowongan = null; // Ganti $hasil menjadi $data_lowongan agar lebih deskriptif
  $syarat = [];

  if (!empty($id) && is_numeric($id)) {
      $query_lowongan = "SELECT lowongan.* , perusahaan.nama_perusahaan, perusahaan.logo 
                         FROM lowongan 
                         JOIN perusahaan ON lowongan.perusahaan_id = perusahaan.id 
                         WHERE lowongan.id = ?";
      $stmt_lowongan_detail = mysqli_prepare($conn, $query_lowongan);
      if ($stmt_lowongan_detail) {
          mysqli_stmt_bind_param($stmt_lowongan_detail, "i", $id);
          mysqli_stmt_execute($stmt_lowongan_detail);
          $result_lowongan_detail = mysqli_stmt_get_result($stmt_lowongan_detail);
          if ($result_lowongan_detail && mysqli_num_rows($result_lowongan_detail) > 0) {
              $data_lowongan = mysqli_fetch_assoc($result_lowongan_detail);
              $syarat = explode("; ", $data_lowongan['syarat'] ?? '');
          } else {
              $pesan_display = "Pekerjaan tidak ditemukan!"; // Gunakan variabel display
              // Opsional: redirect jika pekerjaan tidak ada setelah submit dicegah
              // header("Location: index.php?pesan=" . urlencode($pesan));
              // exit();
          }
          mysqli_stmt_close($stmt_lowongan_detail);
      } else {
          $pesan_display = "Gagal mengambil detail lowongan: " . mysqli_error($conn);
      }
  } else if (empty($id) && !isset($_POST['submit'])) { // Hanya redirect jika bukan POST dan ID kosong
      $pesan_display = "ID Pekerjaan tidak valid.";
      header("Location: index.php?pesan=" . urlencode($pesan));
      exit();
  }


  // Email autofill dari data pencari kerja
  $data_pencari_kerja = null; // Ganti $data menjadi $data_pencari_kerja
  if (!empty($id_pelamar)) { // id_pelamar dari session
      $query_pencari = "SELECT * from pencari_kerja WHERE id = ?";
      $stmt_pencari = mysqli_prepare($conn, $query_pencari);
      if ($stmt_pencari) {
          mysqli_stmt_bind_param($stmt_pencari, "i", $id_pelamar); // Bind sebagai integer
          mysqli_stmt_execute($stmt_pencari);
          $result_pencari = mysqli_stmt_get_result($stmt_pencari);
          $data_pencari_kerja = mysqli_fetch_assoc($result_pencari);
          mysqli_stmt_close($stmt_pencari);
      } else {
          $pesan_display = "Gagal mengambil data pelamar: " . mysqli_error($conn); // Gunakan variabel display
      }
  }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
    <link rel="stylesheet" href="assets/css/global-styles.css" />
    <link rel="stylesheet" href="assets/css/apply.css" />
    <link rel="stylesheet" href="assets/css/time.css" />
    <script src="assets/js/time.js"></script>
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
              if (isset($_SESSION['username'])) {
                echo '<a href="logout.php">Logout</a>';
              } else {
                echo '<a href="login.php">Login</a>';
              }
            ?>
          </li>
        </ul>
        <a href="dashboard-worker.php"> <?php
              if(isset($_SESSION['logo']) && !empty($_SESSION['logo']) && file_exists($_SESSION['logo'])) {
                echo '<img src="' . htmlspecialchars($_SESSION['logo']) . '" alt="profilepict" class="profilepicture" />';
              } else {
                echo '<img src="img/ProfilePicture.jpg" alt="profilepict" class="profilepicture" />';
              }
          ?>
        </a>
      </nav>
    </header>
    <main>
        <?php if (!empty($pesan_display)): ?>
            <div class="alert alert-danger" style="margin-bottom: 15px; padding: 10px; border-radius: 4px; border: 1px solid transparent; color: #a94442; background-color: #f2dede; border-color: #ebccd1; text-align: left;"><?= $pesan_display; ?></div>
        <?php endif; ?>

      <div class="clock-container">
        <div class="clock-time" id="clock">00:00:00</div>
        <div class="clock-date" id="date">Loading date...</div>
      </div>
      <section id="welcome">
        <div class="breadcrumb-bar">
          <div class="breadcrumb-text">
          <div class="breadcrumb-box">
            <div class="breadcrumb-text-inactive">
              <a href="dashboard-worker.php">Dashboard</a>
            </div>  
            <span class="separator-breadcrumb">></span>
            <div class="breadcrumb-text-inactive">
              <a href="index.php">Home</a>
            </div>  
            <span class="separator-breadcrumb">></span>
            <div class="breadcrumb-text-inactive">
              <a href="#">Detail</a>
            </div>
            <div class="breadcrumb-text">
              <a href="#">Apply</a>
            </div>
          </div>
          </div>
          <a href="<?= !empty($id) ? 'detail.php?id=' . htmlspecialchars($id) : 'index.php' ?>" class="btn-back">Kembali</a>
        </div>
      </section>

    <?php if ($data_lowongan): ?>
      <section class="main-container">
        <div class="job-container">
          <div class="company-logo">
            <img src="<?= (isset($data_lowongan['logo']) && !empty($data_lowongan['logo']) && file_exists($data_lowongan['logo'])) ? htmlspecialchars($data_lowongan['logo']) : 'assets/img/ProfilePicture.jpg' ?>" alt="Logo Perusahaan">
          </div>
          <div class="job-details">
            <h1><?= htmlspecialchars($data_lowongan['nama_pekerjaan']); ?></h1>
            <p class="company-name"><?= htmlspecialchars($data_lowongan["nama_perusahaan"]); ?></p>
            <p class="location">📍<?= htmlspecialchars($data_lowongan['lokasi']); ?></p>
            <p class="salary">💰<?= htmlspecialchars($data_lowongan['gaji']); ?></p>

            <div class="description">
              <h2>Deskripsi Pekerjaan</h2>
              <p><?= nl2br(htmlspecialchars($data_lowongan['deskripsi'])); ?></p>
            </div>
            <div class="requirements">
              <h4>Syarat Pekerjaan:</h4>
              <ul>
                <?php foreach ($syarat as $item): ?>
                  <li><?= htmlspecialchars(trim($item)); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
        <div class="apply-section">
            <h2>Apply Pekerjaan</h2>
            <form action="apply.php?id=<?= htmlspecialchars($id) ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="pencari_kerja_id" value="<?= htmlspecialchars($id_pelamar) ?>"> <input type="hidden" name="lowongan_id" value="<?= htmlspecialchars($id) ?>"> <div class="form-group">
                  <label for="nama_lengkap">Nama Lengkap</label>
                  <input type="text" id="nama_lengkap" name="nama_lengkap" required value="<?= isset($data_pencari_kerja['nama_lengkap']) ? htmlspecialchars($data_pencari_kerja['nama_lengkap']): htmlspecialchars($_POST['nama_lengkap'] ?? '') ?> ">
                </div>
                <div class="form-group">
                  <label for="tanggal_lahir">Tanggal Lahir</label>
                  <input type="date" id="tanggal_lahir" name="tanggal_lahir" required value="<?= isset($data_pencari_kerja['tanggal_lahir']) ? htmlspecialchars($data_pencari_kerja['tanggal_lahir']) : htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>">
                </div>
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" id="email" name="email" required value="<?= isset($data_pencari_kerja['email']) ? htmlspecialchars($data_pencari_kerja['email']): htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                  <label for="nomor_hp">Nomor HP</label>
                  <input type="text" id="nomor_hp" name="nomor_hp" value="<?= isset($data_pencari_kerja['no_hp']) ? htmlspecialchars($data_pencari_kerja['no_hp']): htmlspecialchars($_POST['nomor_hp'] ?? '') ?>" required pattern="[0-9]+">
                </div>
                <div class="form-group">
                  <label for="cv">CV (PDF/DOCX, max 5 MB)</label>
                  <input type="file" id="cv" name="cv" accept=".pdf,.docx" required>
                </div>
                <?php if (isset($data_lowongan['isPorto']) && $data_lowongan['isPorto']==1): ?>
                <div class="form-group">
                  <label for="portofolio">Portofolio (PDF, max 5 MB)</label>
                  <input type="file" id="portofolio" name="portofolio" accept=".pdf" >
                </div>
                <?php endif;?>
                <div class="form-group">
                  <label for="surat_lamaran">Surat Lamaran (PDF/DOCX, max 5 MB)</label>
                  <input type="file" id="surat_lamaran" name="surat_lamaran" accept=".pdf,.docx">
                </div>
                <button type="submit" name="submit" value="submit" class="btn-apply">Kirim Lamaran</button>
            </form>
        </div>
      </section>
    <?php else: ?>
        <section class="main-container">
            <p style="text-align: center; color: white; font-size: 1.2em; margin-top: 20px;">
                Lowongan pekerjaan tidak ditemukan atau ID tidak valid.
                <a href="index.php" style="color: #add8e6; text-decoration: underline;">Kembali ke Home</a>
            </p>
        </section>
    <?php endif; ?>
    </main>
    <footer>
      <p>&copy; <?= date("Y"); ?> Cari Kerja.com</p>
      <p class="creators">
        Created by:
        <a href="#" target="_blank">Yehezkiel Darren/71231023</a> |
        <a href="#" target="_blank">Phillip Derric Kho/71231002</a> |
        <a href="#" target="_blank">Syendhi Reswara/71231061</a>
      </p>
    </footer>
  </body>
</html>