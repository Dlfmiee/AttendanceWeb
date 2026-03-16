<?php
require_once 'db.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Ahli - Kelab Badminton</title>
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

    <div class="public-split-container">
        <!-- Left Side: Welcome Text -->
        <div class="public-left" style="align-items: center; text-align: center; padding-top: 5rem;">
            <div style="max-width: 550px; z-index: 1;">
                <h2 style="font-size: 3.5rem; margin-bottom: 1.5rem; font-weight: 800; line-height: 1.1;">Jom Sertai<br><span style="color: var(--primary-color);">Kelab Badminton</span></h2>
                <p style="font-size: 1.25rem; line-height: 1.8; color: var(--text-muted);">
                    Lengkapkan maklumat anda di sebelah untuk mendaftar sebagai ahli rasmi Kelab Badminton kami.
                </p>
            </div>
            
            <!-- Decorative Elements -->
            <div style="position: absolute; bottom: 10%; right: 10%; width: 250px; height: 250px; background: radial-gradient(circle, rgba(236, 72, 153, 0.2) 0%, transparent 70%); border-radius: 50%; filter: blur(20px);"></div>
            <div style="position: absolute; top: 20%; left: -5%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(168, 85, 247, 0.2) 0%, transparent 70%); border-radius: 50%; filter: blur(30px);"></div>
        </div>

        <!-- Right Side: Registration Form -->
        <div class="public-right">
            <div class="landing-card">
                <h1>BORANG AHLI</h1>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error" style="margin-bottom: 2rem; text-align: center;">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <form action="process_registration.php" method="POST">
                    <div class="form-group">
                        <label class="info-label" for="ic_no" style="color: var(--text-muted);">Kad Pengenalan :</label>
                        <input type="text" id="ic_no" name="ic_no" class="form-control" 
                               placeholder="No. Kad Pengenalan" required autofocus
                               maxlength="12">
                    </div>

                    <div class="form-group">
                        <label class="info-label" for="name" style="color: var(--text-muted);">Nama Penuh :</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               placeholder="Nama Penuh" required>
                    </div>

                    <div class="form-group">
                        <label class="info-label" for="class" style="color: var(--text-muted);">Kelas :</label>
                        <input type="text" id="class" name="class" class="form-control" 
                               placeholder="Kelas" required>
                    </div>

                    <div style="text-align: center; margin-top: 3rem;">
                        <button type="submit" class="btn btn-outline-primary" style="width: 100%;">DAFTAR SEKARANG</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
