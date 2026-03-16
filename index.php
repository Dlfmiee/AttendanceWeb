<?php
require_once 'db.php';
session_start();

// Fetch settings from database
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$activity_name = $settings['activity_name'] ?? "Aktiviti Sekolah";
$activity_date = date('Y-m-d'); 
$activity_location = $settings['activity_location'] ?? "Lokasi Sekolah";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kehadiran - Student Attendance</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; overflow-x: hidden;">

    <nav class="public-navbar">
        <a href="index.php" class="nav-brand">BADMINTON <span>CLUB</span></a>
        <div class="nav-links">
            <a href="index.php" class="active">Utama</a>
            <a href="register.php" class="btn btn-primary">Daftar Ahli</a>
            <a href="login.php" class="btn btn-outline-primary">Admin Login</a>
        </div>
    </nav>

    <div class="public-split-container">
        <!-- Left Side: About Text -->
        <div class="public-left" style="align-items: center; text-align: center; padding-top: 5rem;">
            <div style="max-width: 550px; z-index: 1;">
                <h2 style="font-size: 3.5rem; margin-bottom: 1.5rem; font-weight: 800; line-height: 1.1;">Kehadiran Ahli<br><span style="color: var(--primary-color);">Kelab Badminton</span></h2>
                <p style="font-size: 1.25rem; line-height: 1.8; color: var(--text-muted); margin-bottom: 2.5rem;">
                    Selamat datang ke sistem kehadiran digital Kelab Badminton. Sila daftarkan kehadiran anda untuk sesi latihan hari ini.
                </p>
                <a href="register.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.1rem; border-radius: 50px;">Daftar Sebagai Ahli</a>
            </div>
            
            <!-- Decorative Elements -->
            <div style="position: absolute; bottom: 10%; right: 10%; width: 250px; height: 250px; background: radial-gradient(circle, rgba(236, 72, 153, 0.2) 0%, transparent 70%); border-radius: 50%; filter: blur(20px);"></div>
            <div style="position: absolute; top: 20%; left: -5%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(168, 85, 247, 0.2) 0%, transparent 70%); border-radius: 50%; filter: blur(30px);"></div>
        </div>

        <!-- Right Side: Attendance Form -->
        <div class="public-right">
            <div class="landing-card">
                <h1>DAFTAR KEHADIRAN</h1>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success" style="margin-bottom: 2rem; text-align: center;">
                        <strong>Berjaya!</strong> Kehadiran direkodkan untuk <?= htmlspecialchars($_GET['name'] ?? 'pelajar') ?>.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error" style="margin-bottom: 2rem; text-align: center;">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <div class="info-row">
                    <span class="info-label">Aktiviti :</span>
                    <div class="info-value"><?= htmlspecialchars($activity_name) ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Tarikh :</span>
                    <div class="info-value"><?= htmlspecialchars($activity_date) ?></div>
                </div>

                <div class="info-row">
                    <span class="info-label">Tempat :</span>
                    <div class="info-value"><?= htmlspecialchars($activity_location) ?></div>
                </div>

                <form action="process_checkin.php" method="POST">
                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label class="info-label" for="ic_no" style="color: var(--text-muted);">Kad Pengenalan :</label>
                        <input type="text" id="ic_no" name="ic_no" class="form-control" 
                               placeholder="Nombor Kad Pengenalan Anda..." required autofocus
                               maxlength="14" style="padding: 1.25rem; font-size: 1.125rem; text-align: center; letter-spacing: 2px;">
                    </div>

                    <div style="text-align: center; margin-top: 2rem;">
                        <button type="submit" class="btn btn-outline-primary">SAYA HADIR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
