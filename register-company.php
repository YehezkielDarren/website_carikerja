<?php
session_start();
require_once 'connection.php';

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_perusahaan']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $foto = $_FILES['logo']['name'];
    $target_dir = "uploads_img/";
    $target_file = $target_dir . basename($foto);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Cek apakah username sudah ada
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        $pesan = "Username sudah terdaftar, silakan gunakan username lain.";
    } else {       
        // Validasi ukuran file
        if ($foto != '') {
            if ($_FILES['logo']['size'] > 5000000) { // 5 MB
                $pesan = "Ukuran file terlalu besar. Maksimal 5 MB.";
                $uploadOk = 0;
            }
        }

        // Cek apakah file gambar valid
        if ($foto != '' && $uploadOk == 1) {
            $check = getimagesize($_FILES['logo']['tmp_name']);
            if ($check === false) {
                $pesan = "File yang diunggah bukan gambar.";
                $uploadOk = 0;
            }
        }

        // Jika tidak ada kesalahan, lanjutkan proses
        if ($uploadOk == 1) {
            // Masukkan ke tabel users
            $query1 = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'perusahaan')";
            if (mysqli_query($conn, $query1)) {
                $user_id = mysqli_insert_id($conn);

                // Masukkan ke tabel perusahaan
                $query2 = "INSERT INTO perusahaan (user_id, nama_perusahaan, lokasi, logo)
                           VALUES ('$user_id', '$nama_lengkap', '$lokasi', '$target_file')";

                if (mysqli_query($conn, $query2)) {
                    // Unggah file logo
                    if ($foto != '') {
                        move_uploaded_file($_FILES['logo']['tmp_name'], $target_file);
                    }

                    $pesan = "Berhasil mendaftarkan akun";
                    header("Location: login.php");
                    exit();
                } else {
                    $pesan = "Gagal menyimpan data perusahaan";
                }
            } else {
                $pesan = "Gagal menyimpan akun";
            }
        }else {
            $pesan = "Gagal mengunggah file logo";
        }
    }
    if (!empty($pesan)) {
        echo "<script>
                showAlert('".htmlspecialchars($pesan)."');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Pencari Kerja</title>
    <link rel="stylesheet" href="style/login.css">
    <script src="script/script.js"></script>
</head>
<body>
    <header>
      <div class="logo">
        <img src="img/LogoHeader1.png" alt="logokerja" />
        <a>Cari Kerja. <span class="small">com</span></a>
      </div>
      
    </header>
    <main>
        <div class="wrapper">
            <div class="alert alert-danger" style="display: <?= empty($pesan) ? 'none' : 'block'; ?>;">
                <?= htmlspecialchars($pesan); ?>
            </div>
            <form action="register-company.php" method="post" enctype="multipart/form-data">
                <h1>Daftar Perusahaan</h1>

                <div class="input-box">
                    <input type="text" name="username" placeholder="Username Perusahaan" required>
                </div>

                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div class="input-box">
                    <input type="text" name="nama_perusahaan" placeholder="Nama Perusahaan" required>
                </div>

                <div class="input-box">
                    <input type="text" name="lokasi" placeholder="Lokasi Perusahaan" required>
                </div>

                <div class="input-box">
                    <label for="logo">Logo Perusahaan:</label><br>
                    <input type="file" name="logo" id="logo" accept="image/*" required>
                </div>

                <button type="submit" class="btn">Daftar</button>

                <div class="register-link">
                    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                </div>
            </form>
        </div>
        <div id="customAlert" class="custom-alert">
            <div id="alertMessage"></div>
            <button onclick="closeAlert()">OK</button>
        </div>
    </main>
</body>
</html>

