<?php
    session_start();
    require_once '../src/includes/connection.php';
    require_once '../src/includes/helpers.php';
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
    $query = "SELECT * FROM lamaran WHERE lowongan_id = ? AND pelamar_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii",$_GET['id'], $_SESSION['id']);
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
    <link rel="stylesheet" href="assets/css/detail.css" />
    <link rel="stylesheet" href="assets/css/time.css" />
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
    <link rel="stylesheet" href="assets/css/global-styles.css" />
    <script src="assets/js/time.js"></script>
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
        <a href="dashboard-worker.php">
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
          <div class="breadcrumb-box">
            <div class="breadcrumb-text-inactive">
              <a href="dashboard-worker.php">Dashboard</a>
            </div>  
            <span class="separator-breadcrumb">></span>
            <div class="breadcrumb-text-inactive">
              <a href="index.php">Home</a>
            </div>  
            <span class="separator-breadcrumb">></span>
            <div class="breadcrumb-text">
              <a href="#">Detail</a>
            </div>
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
      <p>&copy; 2025 Cari Kerja.com</p>
      <p class="creators">
        Created by:
        <a href="#" target="_blank">Yehezkiel Darren/71231023</a> |
        <a href="#" target="_blank">Phillip Derric Kho/71231002</a> |
        <a href="#" target="_blank">Syendhi Reswara/71231061</a>
      </p>
    </footer>
    <script>
      btnAlrApply= document.querySelector('.btn-alrapply');
      btnAlrApply.addEventListener('click', function(event) {
        event.preventDefault(); // Mencegah aksi default link
        alert('Anda sudah melamar pekerjaan ini.');
      });
    </script>
  </body>
</html>