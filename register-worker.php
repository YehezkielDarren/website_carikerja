<?php
session_start();
require_once 'connection.php';

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $foto = $_FILES['foto']['name'];
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
            if ($_FILES['foto']['size'] > 5000000) { // 5 MB
                $pesan = "Ukuran file terlalu besar. Maksimal 5 MB.";
                $uploadOk = 0;
            }
        }

        // Cek apakah file gambar valid
        if ($foto != '' && $uploadOk == 1) {
            $check = getimagesize($_FILES['foto']['tmp_name']);
            if ($check === false) {
                $pesan = "File yang diunggah bukan gambar.";
                $uploadOk = 0;
            }
        }

        // Jika tidak ada kesalahan, lanjutkan proses
        if ($uploadOk == 1) {
            // Masukkan ke tabel users
            $query1 = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'pencari_kerja')";
            if (mysqli_query($conn, $query1)) {
                $user_id = mysqli_insert_id($conn);

                // Masukkan ke tabel pencari kerja
                $query2 = "INSERT INTO pencari_kerja (user_id, nama_lengkap, tanggal_lahir, no_hp, email, foto)
                           VALUES ('$user_id', '$nama_lengkap', '$tanggal_lahir', '$no_hp', '$email', '$target_file')";

                if (mysqli_query($conn, $query2)) {
                    // Unggah file foto
                    if ($foto != '') {
                        move_uploaded_file($_FILES['foto']['tmp_name'], $target_file);
                    }

                    $pesan = "Berhasil mendaftarkan akun";
                    header("Location: login.php");
                    exit();
                } else {
                    $pesan = "Gagal menyimpan data pencari kerja";
                }
            } else {
                $pesan = "Gagal menyimpan akun";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Pencari Kerja</title>
    <link rel="stylesheet" href="style/login.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
    <link rel="stylesheet" href="style/footer.css" />
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
            <form action="register-worker.php" method="post" enctype="multipart/form-data">
                <h1>Daftar Pencari Kerja</h1>

                <div class="input-box">
                    <input type="text" name="username" placeholder="Username" required>
                </div>

                <div class="input-box">
                    <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
                </div>

                <div class="input-box">
                    <input type="text" name="no_hp" placeholder="Nomor Telepon" required>
                </div>

                <div class="input-box">
                    <input type="date" name="tanggal_lahir" required>
                </div>

                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-box">
                    <input type="password" name="password"  id="password" placeholder="Password" required>
                    <i class='bx bx-show toggle-password' onclick="showPassword()"></i>
                </div>

                <div class="foto">
                    <label for="foto" style="color: white;">Upload Foto (opsional)</label>
                    <input type="file" name="foto" id="foto" accept="image/*">
                </div>

                <button type="submit" class="btn" onclick="showAlert('<?= $pesan ?>')">Daftar</button>

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
