<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Cari Kerja.com</title>
    <link rel="stylesheet" href="assets/css/global-styles.css">
    <link rel="stylesheet" href="assets/css/about.css">
    <link rel="icon" type="image/png" href="img/LogoHeader1.png"/>
</head>
<body>
    <header>
        <div class="logo">
            <img src="img/LogoHeader1.png" alt="logokerja" />
            <a href="index.php">Cari Kerja. <span class="small">com</span></a>
        </div>
        <nav>
            <ul type="none">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li> <span class="separator"></span>
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
             <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'pekerja') : ?>
                <a href="dashboard-worker.php">
                    <?php
                        if(isset($_SESSION['logo']) && !empty($_SESSION['logo']) && file_exists($_SESSION['logo'])) {
                            echo '<img src="' . htmlspecialchars($_SESSION['logo']) . '" alt="profilepict" class="profilepicture" />';
                        } else {
                            echo '<img src="img/ProfilePicture.jpg" alt="profilepict" class="profilepicture" />';
                        }
                    ?>
                </a>
             <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'perusahaan') : ?>
                <a href="dashboard-company.php">
                     <img src="<?= htmlspecialchars($_SESSION['logo']) ?>" alt="profilepict" class="profilepicture" />
                </a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <div class="about-container">
            <section class="hero-section">
                <h1>Mengenal Kami di Cari Kerja.com</h1>
                <p class="subtitle">Menghubungkan Talenta dengan Peluang Terbaik</p>
            </section>

            <section class="content-section">
                <h2>Misi Kami</h2>
                <p>
                    <strong>Cari Kerja.com</strong> lahir dari sebuah gagasan sederhana: mempermudah proses pencarian kerja bagi para talenta di Indonesia dan membantu perusahaan menemukan kandidat yang paling tepat. Kami percaya bahwa pekerjaan yang tepat dapat mengubah hidup seseorang, dan kami berkomitmen untuk menjadi jembatan yang menghubungkan antara pencari kerja yang bersemangat dengan perusahaan yang inspiratif. Platform kami dirancang untuk menjadi intuitif, efisien, dan transparan bagi kedua belah pihak.
                </p>
            </section>

            <section class="team-section">
                <h2>Tim di Balik Layar</h2>
                <div class="team-grid">
                    <div class="creator-card">
                        <img src="img/ProfilePicture.jpg" alt="Foto Yehezkiel Darren">
                        <h3>Yehezkiel Darren</h3>
                        <p class="nim">71231023</p>
                    </div>
                    <div class="creator-card">
                        <img src="img/ProfilePicture.jpg" alt="Foto Phillip Derric Kho">
                        <h3>Phillip Derric Kho</h3>
                        <p class="nim">71231002</p>
                    </div>
                    <div class="creator-card">
                        <img src="img/ProfilePicture.jpg" alt="Foto Syendhi Reswara">
                        <h3>Syendhi Reswara</h3>
                        <p class="nim">71231061</p>
                    </div>
                </div>
            </section>
        </div>
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