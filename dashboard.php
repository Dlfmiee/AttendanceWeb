<?php
require_once 'db.php';
checkLogin();

// Fetch summary statistics
$stats = [
    'total_students' => 0,
    'today_present' => 0,
    'today_absent' => 0,
    'today_late' => 0
];

$today = date('Y-m-d');

// Total Students (Only Approved)
$stmt = $pdo->query("SELECT COUNT(*) as count FROM students WHERE status = 'Approved'");
$stats['total_students'] = $stmt->fetch()['count'];

// Pending Applications
$stmt = $pdo->query("SELECT COUNT(*) as count FROM students WHERE status = 'Pending'");
$pending_count = $stmt->fetch()['count'];

// Today's Attendance Stats
$stmt = $pdo->prepare("
    SELECT status, COUNT(*) as count 
    FROM attendance 
    WHERE date = ? 
    GROUP BY status
");
$stmt->execute([$today]);
while ($row = $stmt->fetch()) {
    if ($row['status'] === 'Present') $stats['today_present'] = $row['count'];
    if ($row['status'] === 'Absent') $stats['today_absent'] = $row['count'];
    if ($row['status'] === 'Late') $stats['today_late'] = $row['count'];
}

// Calculate attendance percentage
$total_marked_today = $stats['today_present'] + $stats['today_absent'] + $stats['today_late'];
$attendance_percentage = $stats['total_students'] > 0 && $total_marked_today > 0
    ? round((($stats['today_present'] + $stats['today_late']) / $total_marked_today) * 100) 
    : 0;

// Handle Settings Update
$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    $new_name = trim($_POST['activity_name']);
    $new_location = trim($_POST['activity_location']);
    
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'activity_name'");
    $stmt->execute([$new_name]);
    
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'activity_location'");
    $stmt->execute([$new_location]);
    
    $success_msg = "Settings updated successfully!";
}

// Fetch current settings
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Attendance</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">BADMINTON <span>CLUB</span></a>
        <div class="nav-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="manage_students.php">Students</a>
            <a href="approve_members.php">Kelulusan Ahli <?php if($pending_count > 0): ?><span style="background: var(--danger-color); color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.75rem; vertical-align: middle; margin-left: 5px;"><?= $pending_count ?></span><?php endif; ?></a>
            <a href="mark_attendance.php">Mark Attendance</a>
            <a href="view_reports.php">Reports</a>
            <a href="logout.php" class="btn btn-danger" style="padding: 0.5rem 1rem; margin-left: 1rem; color: white;">Logout</a>
        </div>
    </nav>

    <div class="container">

        <!-- Welcome Section -->
        <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 1.875rem; color: var(--text-main);">Welcome back, Admin <?= htmlspecialchars($_SESSION['name']) ?></h1>
                <p style="color: var(--text-muted);">Here's what's happening today, <?= date('F j, Y') ?>.</p>
            </div>
            
            <?php if($pending_count > 0): ?>
            <a href="approve_members.php" style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger-color); padding: 1rem 1.5rem; border-radius: 12px; text-decoration: none; display: flex; align-items: center; gap: 1rem; transition: background 0.2s;">
                <div style="background: var(--danger-color); color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.25rem;">
                    <?= $pending_count ?>
                </div>
                <div>
                    <h3 style="color: #f8fafc; font-size: 1rem; margin: 0;">Permohonan Baru</h3>
                    <p style="color: var(--danger-color); font-size: 0.875rem; margin: 0;">Sila semak untuk kelulusan</p>
                </div>
            </a>
            <?php endif; ?>
        </div>

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-title">Jumlah Ahli Kelab</div>
                <div class="stat-value"><?= $stats['total_students'] ?></div>
            </div>
            
            <div class="stat-card" style="border-left-color: var(--secondary-color);">
                <div class="stat-title">Hadir Hari Ini</div>
                <div class="stat-value" style="color: var(--secondary-color);"><?= $stats['today_present'] ?></div>
            </div>

            <div class="stat-card" style="border-left-color: var(--danger-color);">
                <div class="stat-title">Tidak Hadir</div>
                <div class="stat-value" style="color: var(--danger-color);"><?= $stats['today_absent'] ?></div>
            </div>

            <div class="stat-card" style="border-left-color: var(--warning-color);">
                <div class="stat-title">Lewat</div>
                <div class="stat-value" style="color: var(--warning-color);"><?= $stats['today_late'] ?></div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Quick Actions -->
            <div class="card">
                <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Tindakan Pantas</h2>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="mark_attendance.php" class="btn btn-primary">Tanda Kehadiran Hari Ini</a>
                    <a href="manage_students.php" class="btn btn-secondary">Tambah Ahli Baru</a>
                    <a href="view_reports.php" class="btn btn-secondary">Lihat Laporan Kehadiran</a>
                </div>
            </div>

            <!-- Attendance Overview Chart Area -->
            <div class="card">
                <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Peratusan Hari Ini</h2>
                <div style="display: flex; align-items: center; justify-content: center; height: 150px;">
                    <div style="text-align: center;">
                        <span style="font-size: 4rem; font-weight: 700; color: <?= $attendance_percentage >= 80 ? 'var(--secondary-color)' : ($attendance_percentage >= 60 ? 'var(--warning-color)' : 'var(--danger-color)') ?>;">
                            <?= $attendance_percentage ?>%
                        </span>
                        <p style="color: var(--text-muted); margin-top: -10px;">Kadar Kehadiran</p>
                    </div>
                </div>
                <?php if($total_marked_today < $stats['total_students']): ?>
                    <p style="text-align: center; font-size: 0.875rem; color: var(--warning-color); margin-top: 1rem;">
                        Waiting on <?= $stats['total_students'] - $total_marked_today ?> student records.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Settings -->
        <div class="card">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Live Activity Settings (Public Page)</h2>
            <form method="POST" action="" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: flex-end;">
                <input type="hidden" name="action" value="update_settings">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="activity_name">Nama Aktiviti</label>
                    <input type="text" id="activity_name" name="activity_name" class="form-control" value="<?= htmlspecialchars($settings['activity_name'] ?? '') ?>" placeholder="Cth: Latihan Badminton Mingguan" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="activity_location">Tempat/Lokasi</label>
                    <input type="text" id="activity_location" name="activity_location" class="form-control" value="<?= htmlspecialchars($settings['activity_location'] ?? '') ?>" placeholder="Cth: Dewan Sukan" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Public Page</button>
            </form>
            <p style="margin-top: 1rem; font-size: 0.875rem; color: var(--text-muted);">These details appear on the split-screen public check-in page for students.</p>
        </div>
    </div>
</body>
</html>
