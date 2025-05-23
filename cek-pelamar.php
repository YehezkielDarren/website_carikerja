<?php
    session_start();

    if (!isset($_SESSION['username']) || isset($_SESSION['role'])){
        header("location: login.php");
        exit();
    }
    if ($_SESSION['role']=="pencari_kerja"){
        header("location: index.php");
        exit();
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Pelamar</title>
</head>
<body>
    
</body>
</html>

