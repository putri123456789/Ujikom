<?php
session_start();
session_destroy(); // Hapus semua session

// Simpan pesan di sessionStorage
echo "<script>
        sessionStorage.setItem('logoutSuccess', 'true');
        window.location.href = 'login.php';
      </script>";
exit();
?>

