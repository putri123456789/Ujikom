<?php
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Tambah tugas utama
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task'])) {
    $task = $_POST['task'];
    $priority = $_POST['priority'];
    $deadline = $_POST['deadline'];

    $conn->query("INSERT INTO tasks (user_id, task, priority, deadline, status, parent_id) 
                  VALUES ('$user_id', '$task', '$priority', '$deadline', 'pending', NULL)");
    header("Location: index.php");
    exit();
}

// Tambah subtugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subtask']) && isset($_POST['parent_id'])) {
    $subtask = $_POST['subtask'];
    $parent_id = $_POST['parent_id'];

    $conn->query("INSERT INTO tasks (user_id, task, priority, deadline, status, parent_id) 
                  VALUES ('$user_id', '$subtask', 'low', NULL, 'pending', '$parent_id')");
    header("Location: index.php");
    exit();
}
// Update status tugas utama atau subtugas ketika checkbox diubah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];

    // Ambil status tugas saat ini
    $result = $conn->query("SELECT status FROM tasks WHERE id='$task_id'");
    $task = $result->fetch_assoc();

    // Toggle status antara 'pending' dan 'done'
    $new_status = ($task['status'] == 'done') ? 'pending' : 'done';

    // Update status di database
    $conn->query("UPDATE tasks SET status='$new_status' WHERE id='$task_id'");

    // Jika semua subtugas sudah selesai, tandai tugas utama selesai juga
    $parent_check = $conn->query("SELECT parent_id FROM tasks WHERE id='$task_id'")->fetch_assoc();
    if ($parent_check['parent_id'] !== NULL) {
        $parent_id = $parent_check['parent_id'];
        $remaining_subtasks = $conn->query("SELECT COUNT(*) as count FROM tasks WHERE parent_id='$parent_id' AND status='pending'")->fetch_assoc();
        
        if ($remaining_subtasks['count'] == 0) {
            $conn->query("UPDATE tasks SET status='done' WHERE id='$parent_id'");
        } else {
            $conn->query("UPDATE tasks SET status='pending' WHERE id='$parent_id'");
        }
    }

    header("Location: index.php");
    exit();
}

// Hapus tugas utama atau subtugas
if (isset($_GET['delete'])) {
    $task_id = $_GET['delete'];

    // Hapus semua subtugas terlebih dahulu jika yang dihapus adalah tugas utama
    $conn->query("DELETE FROM tasks WHERE parent_id = '$task_id'");
    
    // Hapus tugas utama atau subtugasnya
    $conn->query("DELETE FROM tasks WHERE id = '$task_id'");
    
    header("Location: index.php");
    exit();
}

// Edit tugas utama atau subtugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_task_id'])) {
    $edit_task_id = $_POST['edit_task_id'];
    $new_task = $_POST['new_task'];
    $new_priority = $_POST['new_priority'] ?? 'low'; // Default low jika tidak ada priority
    $new_deadline = $_POST['new_deadline'] ?? NULL;

    // Update tugas di database
    $conn->query("UPDATE tasks SET task='$new_task', priority='$new_priority', deadline='$new_deadline' WHERE id='$edit_task_id'");
    
    header("Location: index.php");
    exit();
}

// Ambil daftar tugas dari database
$result = $conn->query("SELECT * FROM tasks WHERE user_id=$user_id ORDER BY deadline ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>To-Do List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #B4D4FF;
            font-family: 'Arial', sans-serif;
            margin: 0;
        }

        .container {
            background: #FFF8DC;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 800px;
            text-align: center;
            margin-top: 20px;
        }

        .task-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
        }

        .task-item {
            background: #FFF8DC;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            width: 250px;
            position: relative;
        }

        .task-actions {
            /*position: absolute;*/
            top: 10px;
            right: 10px;
            display: flex;
            justify-content: end;
            gap: 8px;
        }

        .edit-btn, .delete-btn {
            text-decoration: none;
            font-size: 16px;
        }

        .edit-btn { color: #007bff; }
        .delete-btn { color: red; }

        .task-content {
            display: flex;
            /* align-items: center; */
            gap: 10px;
        }

        .task-content input[type="checkbox"] {
            transform: scale(1.2);
            cursor: pointer;
            margin-right: 10px; /* Supaya checkbox ada di kiri */
        }

        .priority {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            display: inline-block;
            margin-top: 5px;
            color: white;
        }

        .low { background:rgba(61, 212, 66, 0.78); }
        .medium { background:rgba(255, 153, 0, 0.7); }
        .high { background:rgba(244, 67, 54, 0.65); }

        .subtask-list {
            padding: 0;
            margin-top: 10px;
        }

        .subtask-item {
            background: #F5F5DC;
            padding: 10px;
            margin: 5px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .subtask-item input[type="checkbox"] {
            transform: scale(1.2);
            cursor: pointer;
            margin-right: 10px; /* Supaya checkbox ada di kiri */
        }

        .subtask-item .delete-btn {
            margin-left: auto;
        }

        input, select, button {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 16px;
            outline: none;
        }

        button {
            background: #007bff;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #007bff;

        }
        .deadline {
    font-weight: bold;
    margin-top: 5px;
}

.deadline.late {
    color: red;
}

.warning {
    background: red;
    color: white;
    padding: 2px 5px;
    border-radius: 5px;
    font-size: 12px;
    margin-left: 5px;
}

    </style>
</head>
<body>
<div style="position: absolute; top: 10px; right: 20px;">
    <a href="logout.php" onclick="return confirmLogout()">
        <i class="fas fa-sign-out-alt"></i>Logout</a>
</div>
<!-- Form tambah tugas utama di dalam kotak putih -->
<div class="container">
    <h2>To-Do List</h2>
    <form method="POST">
        <input type="text" name="task" placeholder="Tambah tugas baru..." required>
        <select name="priority">
            <option value="low">Rendah</option>
            <option value="medium">Sedang</option>
            <option value="high">Tinggi</option>
        </select>
        <input type="date" name="deadline" required>
        <button type="submit">Tambah</button>
    </form>
</div>

<!-- Daftar tugas utama dan subtugas di luar kotak putih -->
<div class="task-container">
    <?php while ($row = $result->fetch_assoc()): ?>
        <?php if ($row['parent_id'] === NULL): ?>
            <div class="task-item">
                <div class="tes">
                     <div class="task-actions">
                    <a href="edit.php?id=<?= $row['id'] ?>" class="edit-btn"><i class="fas fa-edit"></i></a>
                    <a href="?delete=<?= $row['id'] ?>" class="delete-btn"><i class="fas fa-trash-alt"></i></a>
                </div>
                </div>

                <form method="POST" class="task-content">
                    <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
                    <input type="checkbox" name="update" onchange="this.form.submit()" <?= $row['status'] == 'done' ? 'checked' : '' ?>>
                    <span><?= $row['task'] ?></span>
                </form>

                <div class="deadline <?= (strtotime($row['deadline']) < time() && $row['status'] == 'pending') ? 'late' : '' ?>">Deadline: <?= $row['deadline'] ?>
                    <?php if (strtotime($row['deadline']) < time() && $row['status'] == 'pending'): ?>
                    <span class="warning">Terlambat!</span>
                    <?php endif; ?>
                </div>
                <span class="priority <?= $row['priority'] ?>"><?= ucfirst($row['priority']) ?></span>

                <ul class="subtask-list">
                    <?php
                    $subtasks = $conn->query("SELECT * FROM tasks WHERE parent_id = {$row['id']}");
                    while ($subtask = $subtasks->fetch_assoc()):
                    ?>
                        <li class="subtask-item">
                            <form method="POST" class="task-content">
                                <input type="hidden" name="task_id" value="<?= $subtask['id'] ?>">
                                <input type="checkbox" onchange="this.form.submit()" <?= $subtask['status'] == 'done' ? 'checked' : '' ?>>
                                <span style="<?= $subtask['status'] == 'done' ? 'text-decoration: line-through; color: gray;' : '' ?>">
                                    <?= $subtask['task'] ?>
                                </span>
                            </form>
                            <a href="?delete=<?= $subtask['id'] ?>" class="delete-btn"><i class="fas fa-trash-alt"></i></a>
                        </li>
                    <?php endwhile; ?>
                </ul>

                <form method="POST">
                    <input type="hidden" name="parent_id" value="<?= $row['id'] ?>">
                    <input type="text" name="subtask" placeholder="Tambah subtugas..." required>
                    <button type="submit">Tambah</button>
                </form>
            </div>
        <?php endif; ?>
    <?php endwhile; ?>
</div>
<script>
    if (sessionStorage.getItem('loginSuccess')) {
        alert('Login berhasil! Selamat datang 😊');
        sessionStorage.removeItem('loginSuccess'); // Hapus supaya tidak muncul lagi saat refresh
    }
    function confirmLogout() {
        return confirm("Anda yakin mau logout?");
    }
    function confirmLogout() {
        return confirm("Anda yakin mau logout?");
    }
    document.addEventListener("DOMContentLoaded", function() {
        let lateTasks = document.querySelectorAll('.deadline.late');
        if (lateTasks.length > 0) {
            alert("Ada tugas yang sudah terlambat! Segera selesaikan.");
        }
    });
</script>
</body>
</html>