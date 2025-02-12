<?php
include 'koneksi.php'; // Hubungkan ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form login
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Periksa apakah username ada di database
    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verifikasi password yang diinput dengan password yang tersimpan
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id']; // Simpan user_id dalam session
            
            // Tambahkan sessionStorage untuk pop-up
            echo "<script>
                    sessionStorage.setItem('loginSuccess', 'true');
                    window.location.href = 'index.php';
                  </script>";
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #B4D4FF;
            font-family: Arial, sans-serif;
        }
        .login-container {
            background: #FFF8DC;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
        }
        input {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007bff; 
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background: #0056b3;
        }
        p {
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Belum punya akun? <a href="register.php">Register</a></p>
</div>
<script>
    if (sessionStorage.getItem('registerSuccess')) {
        alert('Registrasi berhasil! Silakan login 😊');
        sessionStorage.removeItem('registerSuccess'); // Hapus supaya tidak muncul lagi saat refresh
    }
    if (sessionStorage.getItem('logoutSuccess')) {
        alert('Logout berhasil! Sampai jumpa lagi 👋');
        sessionStorage.removeItem('logoutSuccess'); // Hapus supaya tidak muncul lagi saat refresh
    }
</script>
</body>
</html>
