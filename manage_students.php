<?php
require_once 'db.php';
checkLogin();

$error = '';
$success = '';

// Handle Delete Student
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Ahli berjaya dipadam.';
    } catch (PDOException $e) {
        $error = 'Gagal memadam ahli (Sila pastikan rekod kehadiran dipadam terlebih dahulu).';
    }
}

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $ic_no = trim($_POST['ic_no']);
    $name = trim($_POST['name']);
    $class = trim($_POST['class']);

    if (empty($ic_no) || empty($name) || empty($class)) {
        $error = 'Semua ruangan wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (ic_no, name, class, status) VALUES (?, ?, ?, 'Approved')");
            $stmt->execute([$ic_no, $name, $class]);
            $success = 'Ahli berjaya ditambah.';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Ahli dengan No. KP ini sudah wujud.';
            } else {
                $error = 'Ralat pangkalan data: ' . $e->getMessage();
            }
        }
    }
}

// Fetch Students
$stmt = $pdo->query("SELECT * FROM students ORDER BY class, name");
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urus Ahli - Kehadiran Ahli</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Pyour<span>Attendance</span></a>
        <div class="nav-links">
            <a href="dashboard.php">Papan Pemuka</a>
            <a href="manage_students.php" class="active">Ahli</a>
            <a href="approve_members.php">Kelulusan Ahli</a>
            <a href="mark_attendance.php">Tanda Kehadiran</a>
            <a href="view_reports.php">Laporan</a>
            <a href="logout.php" class="btn btn-danger" style="padding: 0.5rem 1rem; margin-left: 1rem; color: white;">Log Keluar</a>
        </div>
    </nav>

    <div class="container">

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr;">
            <!-- Add Student Form -->
            <div class="card">
                <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Tambah Ahli Baru</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label class="form-label" for="ic_no">No. Kad Pengenalan</label>
                        <input type="text" id="ic_no" name="ic_no" class="form-control" required placeholder="Masukkan 12 digit No. KP">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="name">Nama Penuh</label>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="Masukkan nama penuh ahli">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="class">Kelas</label>
                        <input type="text" id="class" name="class" class="form-control" required placeholder="Masukkan nama kelas">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Tambah Ahli</button>
                </form>
            </div>

            <!-- Student List -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.25rem;">Senarai Ahli Berdaftar (<?= count($students) ?>)</h2>
                </div>
                
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Kad Pengenalan</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($students) > 0): ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['ic_no']) ?></td>
                                        <td><?= htmlspecialchars($student['name']) ?></td>
                                        <td><span class="badge" style="background: var(--bg-color); color: var(--text-muted);"><?= htmlspecialchars($student['class']) ?></span></td>
                                        <td>
                                            <a href="?delete=<?= $student['id'] ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="return confirm('Adakah anda pasti mahu memadam ahli ini? Semua rekod kehadiran mereka juga akan dipadamkan.');">Padam</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center" style="color: var(--text-muted); padding: 2rem;">Tiada ahli berdaftar lagi. Sila isi borang di sebelah.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
