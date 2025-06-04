<?php
    session_start();
    session_destroy();
?>
<script>
    window.location.href = "login.php";
    alert("Anda telah berhasil logout.");
</script>
