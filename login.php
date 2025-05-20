<?php
session_start();
require_once 'connection.php';

$pesan = "";

// Cek apakah sudah login
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result); // Ambil 1x saja
        $_SESSION['role'] = $row['role'];
        $_SESSION['user_id']=$row['id'];
        

        if ($_SESSION['role'] == 'perusahaan') {
            $sql = "SELECT * FROM perusahaan WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $row['id']);
            mysqli_stmt_execute($stmt);
            $result2 = mysqli_stmt_get_result($stmt);
            if ($row2 = mysqli_fetch_assoc($result2)) {
                $_SESSION['username'] = $row2['nama_perusahaan'];
                $_SESSION['logo'] = $row2['logo'];
                $_SESSION['id'] = $row2['id'];
            }
            header("Location: dashboard-company.php");
            exit();

        } elseif ($_SESSION['role'] == 'pencari_kerja') {
            $sql = "SELECT * FROM pencari_kerja WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $row['id']);
            mysqli_stmt_execute($stmt);
            $result2 = mysqli_stmt_get_result($stmt);
            if ($row2 = mysqli_fetch_assoc($result2)) {
                $_SESSION['username'] = $row2['nama_lengkap'];
                $_SESSION['logo'] = $row2['foto'];
                $_SESSION['id'] = $row2['id'];
            }
            header("Location: index.php");
            exit();
        }
    } else {
        $pesan = "Username atau password salah!";
    }    
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style/login.css">
    <link rel="stylesheet" href="style/time.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
    <script src="script/script.js"></script>
    <script src="script/time.js"></script>
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
            <!-- Pesan error dari PHP -->
            <div class="alert alert-danger" id="errorMsg" style="display: <?= empty($pesan) ? 'none' : 'block'; ?>;">
                <?= htmlspecialchars($pesan); ?>
            </div>

            <form action="login.php" method="post" onsubmit="return checkLogin()">
                <h1>Login</h1>

                <div class="input-box">
                    <input type="text" name="username" id="username" placeholder="Username">
                    <i class='bx bx-user'></i>
                </div>

                <div class="input-box">
                    <input type="password" name="password" id="password" placeholder="Password">
                    <i class='bx bx-show toggle-password' onclick="showPassword()"></i>
                </div>

                <button type="submit" class="btn" onclick="">Login</button>

                <div class="register-link">
                    <a href="register-worker.php">Register as worker</a> | <a href="register-company.php">Register a company</a><br><br>
                    <a href="index.php">Continue as a guest</a>
                </div>
            </form>
        </div>
    </main>
    <div class="clock-container">
      <div class="clock-time" id="clock">00:00:00</div>
      <div class="clock-date" id="date">Loading date...</div>
    </div>
</body>
</html>
