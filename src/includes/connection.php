<?php
    $server_name = "localhost";
    $username = "root";
    $password = "";
    $database = "carikerjaweb_db";
    $conn = mysqli_connect($server_name, $username, $password, $database) or die("Connection failed: " . mysqli_connect_error());
?>
