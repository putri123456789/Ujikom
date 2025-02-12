<?php
include 'koneksi.php'; // Hubungkan ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password sebelum disimpan

    // Cek apakah username sudah ada
    $result = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($result->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Simpan user baru ke database
        $conn->query("INSERT INTO users (username, password) VALUES ('$username', '$password')");

        // Tambahkan sessionStorage untuk pop-up
        echo "<script>
                sessionStorage.setItem('registerSuccess', 'true');
                window.location.href = 'login.php';
              </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Register</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #B4D4FF;
            font-family: Arial, sans-serif;
        }
        .register-container {
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

<div class="register-container">
    <h2>Register</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>
    <p>Sudah punya akun? <a href="login.php">Login</a></p>
</div>

</body>
</html>
