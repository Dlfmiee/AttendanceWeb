<?php
session_start();
require_once 'db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Sila masukkan kedua-dua nama pengguna dan kata laluan.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Use password_verify since we hashed the $2y$10 password in our SQL
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            redirect('dashboard.php');
        } else {
            $error = 'Nama pengguna atau kata laluan salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk Admin - Sistem Kehadiran Pelajar</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; overflow-x: hidden;">

    <nav class="public-navbar">
        <a href="index.php" class="nav-brand">Pyour<span>Attendance</span></a>
        <div class="nav-links">
            <a href="index.php">Utama</a>
            <a href="register.php" class="btn btn-primary">Daftar Ahli</a>
            <a href="login.php" class="btn btn-primary">Log Masuk Admin</a>
        </div>
    </nav>

    <div class="login-container" style="padding-top: 5rem;">
        <div class="card login-card">
            <div class="text-center mb-4">
                <h1 style="color: var(--primary-color); font-size: 1.5rem; margin-bottom: 0.5rem;">Log Masuk Admin</h1>
                <p style="color: var(--text-muted); font-size: 0.875rem;">Sila log masuk untuk mengurus kehadiran ahli Kelab Badminton</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Nama Pengguna</label>
                    <input type="text" id="username" name="username" class="form-control" required autofocus placeholder="Masukkan nama pengguna">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Kata Laluan</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Masukkan kata laluan">
                </div>

                <div class="form-group pt-2">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Log Masuk</button>
                </div>
            </form>
            <!-- End of Login Form -->
        </div>
    </div>
</body>
</html>
