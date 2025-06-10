<?php
  session_start();
  require_once '../src/includes/connection.php';
  require_once '../src/includes/helpers.php';

  if (!isset($_SESSION['username']) || $_SESSION['role'] != 'perusahaan') {
    header("Location: index.php");
    exit();
  }
  $user_id = $_SESSION['id'];
  $pesan = ""; 
  $pesan_kosong = ""; 
  $pesan_operasi = "";
  $pesan_hapus=""; 
  $job_to_edit = null;
  $is_editing = false;

  // --- Handle Edit Request (GET) ---
  if (isset($_GET['edit_id']) && is_numeric($_GET['edit_id'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $sql_edit = "SELECT * FROM lowongan WHERE id = '$edit_id' AND perusahaan_id = '$user_id'";
    $result_edit = mysqli_query($conn, $sql_edit);
    if ($result_edit && mysqli_num_rows($result_edit) > 0) {
      $job_to_edit = mysqli_fetch_assoc($result_edit);
      $is_editing = true;
    } else {
      $pesan_operasi = "Lowongan untuk diedit tidak ditemukan atau Anda tidak berhak mengeditnya.";
    }
  }

  // --- Handle Update Lowongan (POST) ---
  if (isset($_POST['submit_update_lowongan'])) {
    $lowongan_id_update = mysqli_real_escape_string($conn, $_POST['lowongan_id_update']);
    $nama_pekerjaan = mysqli_real_escape_string($conn, $_POST['nama_pekerjaan']);
    $jenis_pekerjaan = mysqli_real_escape_string($conn, $_POST['jenis_pekerjaan']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $gaji = mysqli_real_escape_string($conn, $_POST['gaji']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $syarat = mysqli_real_escape_string($conn, $_POST['syarat']);
    $tanggal_batas = mysqli_real_escape_string($conn, $_POST['tanggal_batas']);
    $isPorto = mysqli_real_escape_string($conn, $_POST['isPorto']);

    // Basic validation can be more thorough
    if (!empty($nama_pekerjaan) && !empty($jenis_pekerjaan) && !empty($kategori) && !empty($lokasi) && !empty($gaji) && !empty($deskripsi) && !empty($syarat) && !empty($tanggal_batas)) {
        $sql_update_query = "UPDATE lowongan SET nama_pekerjaan = '$nama_pekerjaan', jenis_pekerjaan = '$jenis_pekerjaan', kategori = '$kategori', lokasi = '$lokasi', gaji = '$gaji', deskripsi = '$deskripsi', syarat = '$syarat', tanggal_batas = '$tanggal_batas', isPorto = '$isPorto' WHERE id = '$lowongan_id_update' AND perusahaan_id = '$user_id'";
        if (mysqli_query($conn, $sql_update_query)) {
            header("Location: dashboard-company.php?update_status=success");
            exit();
        } else {
            $pesan_operasi = "Gagal mengupdate lowongan: " . mysqli_error($conn);
        }
    } else {
        $pesan_operasi = "Semua field wajib diisi untuk update.";
    }
  }

  // --- Handle Tambah Lowongan (POST) ---
  if (isset($_POST['submit_tambah_lowongan'])) {
    // Ensure this is not an update submission by checking if lowongan_id_update is NOT set
    if (!isset($_POST['lowongan_id_update'])) {
        $nama_pekerjaan_tambah = mysqli_real_escape_string($conn, $_POST['nama_pekerjaan']);
        $jenis_pekerjaan_tambah = mysqli_real_escape_string($conn, $_POST['jenis_pekerjaan']);
        $kategori_tambah = mysqli_real_escape_string($conn, $_POST['kategori']);
        $lokasi_tambah = mysqli_real_escape_string($conn, $_POST['lokasi']);
        $gaji_tambah = mysqli_real_escape_string($conn, $_POST['gaji']);
        $deskripsi_tambah = mysqli_real_escape_string($conn, $_POST['deskripsi']);
        $syarat_tambah = mysqli_real_escape_string($conn, $_POST['syarat']);
        $tanggal_batas_tambah = mysqli_real_escape_string($conn, $_POST['tanggal_batas']);
        $isPorto_tambah = mysqli_real_escape_string($conn, $_POST['isPorto']);
        $tanggal_sekarang = new DateTime();
        $tanggal_buat_db = $tanggal_sekarang->format('Y-m-d');
        // $user_id is already defined as $_SESSION['id']

        if (!empty($nama_pekerjaan_tambah) && !empty($jenis_pekerjaan_tambah) && !empty($kategori_tambah) && !empty($lokasi_tambah) && !empty($gaji_tambah) && !empty($deskripsi_tambah) && !empty($syarat_tambah) && !empty($tanggal_batas_tambah)) {
            $sql_cek_nama = "SELECT id FROM lowongan WHERE nama_pekerjaan = '$nama_pekerjaan_tambah' AND perusahaan_id = '$user_id'";
            $res_cek_nama = mysqli_query($conn, $sql_cek_nama);
            if (mysqli_num_rows($res_cek_nama) > 0) {
                $pesan_operasi = "Lowongan dengan nama pekerjaan tersebut sudah ada untuk perusahaan Anda.";
            } else {
                $sql_insert = "INSERT INTO lowongan (perusahaan_id, nama_pekerjaan, jenis_pekerjaan, kategori, lokasi, gaji, deskripsi, syarat, tanggal_batas, isPorto,tanggal_buat) 
                               VALUES ('$user_id', '$nama_pekerjaan_tambah', '$jenis_pekerjaan_tambah', '$kategori_tambah', '$lokasi_tambah', '$gaji_tambah', '$deskripsi_tambah', '$syarat_tambah', '$tanggal_batas_tambah', '$isPorto_tambah', '$tanggal_buat_db')";
                if (mysqli_query($conn, $sql_insert)) {
                    header("Location: dashboard-company.php?tambah_status=success");
                    exit();
                } else {
                    $pesan_operasi = "Gagal menambah lowongan: " . mysqli_error($conn);
                }
            }
        } else {
            $pesan_operasi = "Semua field wajib diisi untuk menambah lowongan.";
        }
    }
  }

  // --- Handle status messages from redirects ---
  if (isset($_GET['update_status']) && $_GET['update_status'] === 'success') {
    $pesan_operasi = "Lowongan berhasil diupdate!";
  }
  if (isset($_GET['tambah_status']) && $_GET['tambah_status'] === 'success') {
    $pesan_operasi = "Lowongan berhasil ditambahkan!";
  }
  // Example for gagal, if you pass error messages via session or query param
  // if (isset($_GET['tambah_status']) && $_GET['tambah_status'] === 'gagal') {
  //   $pesan_operasi = "Gagal menambahkan lowongan. " . ($_GET['error_msg'] ?? '');
  // }
  if (isset($_GET['hapus_status'])) {
    switch ($_GET['hapus_status']) {
        case 'success':
            $pesan_hapus = "Lowongan berhasil dihapus!";
            break;
        case 'gagal_ada_pelamar':
            $pesan_hapus = "Gagal menghapus: Masih ada pelamar yang terdaftar pada lowongan ini.";
            break;
        case 'gagal_auth':
        case 'gagal_invalid_id':
        case 'gagal_query':
            $pesan_hapus = "Gagal menghapus lowongan. Silakan coba lagi atau hubungi administrator.";
            break;
    }
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
          lowongan.tanggal_buat,
          perusahaan.nama_perusahaan,
          perusahaan.logo
      FROM lowongan
      JOIN perusahaan ON lowongan.perusahaan_id = perusahaan.id
      WHERE (lowongan.tanggal_batas > NOW() AND lowongan.perusahaan_id = '$user_id')
      ORDER BY lowongan.tanggal_batas DESC";

  $result = mysqli_query($conn, $sql);

  $jobList = [];

  if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      $jobList[] = $row;
    }
  } else {
    // Only set $pesan_kosong if no filters are active and no operations just happened that might clear the list temporarily
    if (!isset($_GET['submit_judul']) && !isset($_GET['submit_filter']) && empty($pesan_operasi)) {
        $pesan_kosong = "Anda belum membuat lowongan kerja.";
    }
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
            lowongan.tanggal_buat,
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
  } else {
    $jobListFilter = $jobList;
  }
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="assets/css/dashboard-company.css" />
    <link rel="stylesheet" href="assets/css/global-styles.css" />
    <link rel="stylesheet" href="assets/css/time.css" />
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
    <script src="assets/js/search-filter.js"></script>
    <script src="assets/js/time.js"></script>
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
        <div class="breadcrumb-bar">
          <div class="breadcrumb-box">  
              <div class="breadcrumb-text">
                  <a href="#">Dashboard</a>
              </div>
          </div>
        </div>
        <?php
          if (isset($_SESSION['username'])) {
            echo '<div class="welcome">Dashboard, <span class="username">' . htmlspecialchars($_SESSION['username']) . '</span></div>';
          }
        ?>
      </section>
      <?php if (!empty($pesan_hapus)): ?>
              <div class="alert <?= (strpos(strtolower($pesan_hapus), 'berhasil') !== false || strpos(strtolower($pesan_operasi), 'success') !== false) ? 'alert-success' : 'alert-danger' ?>" style="margin-bottom: 15px; padding: 10px; border-radius: 4px; border: 1px solid transparent;">
                  <?= htmlspecialchars($pesan_hapus); ?>
              </div>
            <?php endif; ?>
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
                      <a href="cek-pelamar.php?id=<?= $job['lowongan_id'] ?>" class="btn-action lihat">👀 Cek Pelamar</a>
                      <a href="dashboard-company.php?edit_id=<?= $job['lowongan_id'] ?>" class="btn-action edit">✏️ Edit</a>
                      <a href="hapus-lowongan.php?id=<?= $job['lowongan_id'] ?>" class="btn-action hapus" onclick="return confirm('Yakin ingin menghapus lowongan ini?')">🗑️ Hapus</a>
                    </div>
                  </div>
                <?php endforeach; ?>
              </section>
          <?php endif; ?>
        </div>

        <div class="add-job-column">
          <section class="add-job-form-container">
            <h2><?= $is_editing ? 'Edit Lowongan Pekerjaan' : 'Tambah Lowongan Pekerjaan Baru' ?></h2>
            <?php if (!empty($pesan_operasi)): ?>
              <div class="alert <?= (strpos(strtolower($pesan_operasi), 'berhasil') !== false || strpos(strtolower($pesan_operasi), 'success') !== false) ? 'alert-success' : 'alert-danger' ?>" style="margin-bottom: 15px; padding: 10px; border-radius: 4px; border: 1px solid transparent;">
                  <?= htmlspecialchars($pesan_operasi); ?>
              </div>
            <?php endif; ?>

            <form action="dashboard-company.php" method="POST">
              <?php if ($is_editing && $job_to_edit): ?>
                <input type="hidden" name="lowongan_id_update" value="<?= htmlspecialchars($job_to_edit['id']) ?>">
              <?php endif; ?>

              <div class="form-group">
                <label for="nama_pekerjaan_form">Nama Pekerjaan:</label>
                <input type="text" id="nama_pekerjaan_form" name="nama_pekerjaan" required value="<?= htmlspecialchars($job_to_edit['nama_pekerjaan'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="jenis_pekerjaan_form">Jenis Pekerjaan:</label>
                <select id="jenis_pekerjaan_form" name="jenis_pekerjaan" required>
                  <option value="">Pilih Jenis Pekerjaan</option>
                  <?php
                    $jenis_opsi = ["Full-time", "Part-time", "Internship", "Contract", "Freelance"];
                    $selected_jenis = $job_to_edit['jenis_pekerjaan'] ?? '';
                    foreach ($jenis_opsi as $opsi) {
                        echo '<option value="' . htmlspecialchars($opsi) . '"' . ($selected_jenis === $opsi ? ' selected' : '') . '>' . htmlspecialchars($opsi) . '</option>';
                    }
                  ?>
                </select>
              </div>
              <div class="form-group">
                <label for="kategori_form">Kategori Pekerjaan:</label>
                <input type="text" id="kategori_form" name="kategori" required placeholder="Contoh: IT, Marketing, Desain" value="<?= htmlspecialchars($job_to_edit['kategori'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="lokasi_form">Lokasi:</label>
                <input type="text" id="lokasi_form" name="lokasi" required value="<?= htmlspecialchars($job_to_edit['lokasi'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="gaji_form">Gaji:</label>
                <input type="text" id="gaji_form" name="gaji" placeholder="Contoh: Rp 5.000.000 atau Kompetitif" required value="<?= htmlspecialchars($job_to_edit['gaji'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label for="deskripsi_form">Deskripsi Pekerjaan:</label>
                <textarea id="deskripsi_form" name="deskripsi" rows="4" required><?= htmlspecialchars($job_to_edit['deskripsi'] ?? '') ?></textarea>
              </div>
              <div class="form-group">
                <label for="syarat_form">Syarat Pekerjaan:</label>
                <textarea id="syarat_form" name="syarat" rows="4" required placeholder="Pisahkan setiap syarat dengan titik koma (;)"><?= htmlspecialchars($job_to_edit['syarat'] ?? '') ?></textarea>
              </div>
              <div class="form-group">
                <label for="tanggal_batas_form">Tanggal Batas Lamaran:</label>
                <input type="date" id="tanggal_batas_form" name="tanggal_batas" required value="<?= htmlspecialchars($job_to_edit['tanggal_batas'] ?? '') ?>" min="<?= date('Y-m-d') ?>">
              </div>
              <div class="form-group">
                <label for="isPorto_form">Perlu Portofolio?</label>
                <select id="isPorto_form" name="isPorto" required>
                  <?php
                    $isPorto_selected = $job_to_edit['isPorto'] ?? '0'; // Default to '0' (Tidak) for new entries
                  ?>
                  <option value="0" <?= ($isPorto_selected == '0' ? 'selected' : '') ?>>Tidak</option>
                  <option value="1" <?= ($isPorto_selected == '1' ? 'selected' : '') ?>>Ya</option>
                </select>
              </div>
              <?php if ($is_editing): ?>
                <button type="submit" name="submit_update_lowongan" class="btn-submit-lowongan">Update Lowongan</button>
                <a href="dashboard-company.php" class="btn-cancel-edit" style="display: inline-block; margin-top: 10px; padding: 10px 15px; background-color: #aaa; color: white; text-decoration: none; border-radius: 4px; text-align:center;">Batal Edit</a>
              <?php else: ?>
                <button type="submit" name="submit_tambah_lowongan" class="btn-submit-lowongan">Tambah Lowongan</button>
              <?php endif; ?>
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