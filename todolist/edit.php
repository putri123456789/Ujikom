<?php
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Cek apakah ada parameter id yang dikirim
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$task_id = $_GET['id'];

// Ambil data tugas berdasarkan ID
$result = $conn->query("SELECT * FROM tasks WHERE id='$task_id' AND user_id='$user_id'");
if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$row = $result->fetch_assoc();

// Update tugas jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];

    $conn->query("UPDATE tasks SET task='$task', priority='$priority', deadline='$deadline' WHERE id='$task_id' AND user_id='$user_id'");
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Tugas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #B4D4FF;
            font-family: 'Arial', sans-serif;
            margin: 0;
        }

        .container {
            background: #FFF8DC;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 16px;
            outline: none;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.3s;
        }

        button:hover {
            background: #0056b3;
        }

        .back-btn {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #333;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Tugas</h2>

    <form method="POST">
        <input type="text" name="task" value="<?= $row['task'] ?>" required>
        <select name="priority">
            <option value="low" <?= $row['priority'] == 'low' ? 'selected' : '' ?>>Rendah</option>
            <option value="medium" <?= $row['priority'] == 'medium' ? 'selected' : '' ?>>Sedang</option>
            <option value="high" <?= $row['priority'] == 'high' ? 'selected' : '' ?>>Tinggi</option>
        </select>
        <input type="date" name="deadline" value="<?= $row['deadline'] ?>" required>
        <button type="submit">Simpan Perubahan</button>
    </form>

    <a href="index.php" class="back-btn">Batal</a>
</div>

</body>
</html>