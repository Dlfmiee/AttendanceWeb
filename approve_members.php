<?php
require_once 'db.php';
checkLogin();

$error = '';
$success = '';

// Handle Status Updates (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $student_id = (int)$_POST['student_id'];
    
    if ($_POST['action'] === 'approve') {
        try {
            $stmt = $pdo->prepare("UPDATE students SET status = 'Approved' WHERE id = ?");
            $stmt->execute([$student_id]);
            $success = "Keahlian telah diluluskan.";
        } catch (PDOException $e) {
            $error = "Ralat pangkalan data: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'reject') {
        try {
            $stmt = $pdo->prepare("UPDATE students SET status = 'Rejected' WHERE id = ?");
            $stmt->execute([$student_id]);
            $success = "Permohonan keahlian telah ditolak.";
        } catch (PDOException $e) {
            $error = "Ralat pangkalan data: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
            $stmt->execute([$student_id]);
            $success = "Rekod permohonan telah dipadam.";
        } catch (PDOException $e) {
            $error = "Ralat memadam rekod.";
        }
    }
}

// Fetch Pending Students
$stmt = $pdo->query("SELECT * FROM students WHERE status = 'Pending' ORDER BY created_at DESC");
$pending_students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelulusan Keahlian - Attendance Manager</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">BADMINTON <span>CLUB</span></a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_students.php">Students</a>
            <a href="approve_members.php" class="active">Kelulusan Ahli</a>
            <a href="mark_attendance.php">Mark Attendance</a>
            <a href="view_reports.php">Reports</a>
            <a href="logout.php" class="btn btn-danger" style="padding: 0.5rem 1rem; margin-left: 1rem; color: white;">Logout</a>
        </div>
    </nav>

    <div class="container">

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem;">Permohonan Baru Keahlian (<?= count($pending_students) ?>)</h2>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Tarikh Mohon</th>
                            <th>No Kad Pengenalan</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pending_students) > 0): ?>
                            <?php foreach ($pending_students as $student): ?>
                                <tr>
                                    <td><span style="color: var(--text-muted); font-size: 0.875rem;"><?= htmlspecialchars(date('d M Y, H:i', strtotime($student['created_at']))) ?></span></td>
                                    <td><?= htmlspecialchars($student['ic_no']) ?></td>
                                    <td><strong><?= htmlspecialchars($student['name']) ?></strong></td>
                                    <td><span class="badge" style="background: var(--bg-color); color: var(--text-muted);"><?= htmlspecialchars($student['class']) ?></span></td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-primary" style="padding: 0.35rem 0.75rem; font-size: 0.875rem; background: #10b981;">Luluskan</button>
                                            </form>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="btn btn-danger" style="padding: 0.35rem 0.75rem; font-size: 0.875rem; background: #ef4444;" onclick="return confirm('Tolak permohonan ini?');">Tolak</button>
                                            </form>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.875rem;" onclick="return confirm('Padam rekod secara kekal?');">Padam</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center" style="color: var(--text-muted); padding: 3rem;">Tiada permohonan baru pada masa ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
