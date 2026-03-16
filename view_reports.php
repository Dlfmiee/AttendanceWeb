<?php
require_once 'db.php';
checkLogin();

// Generate report data based on filters
$report_type = $_GET['type'] ?? 'date'; // 'date' or 'student'
$filter_date = $_GET['date'] ?? date('Y-m-d');
$filter_student = $_GET['student_id'] ?? '';

// Fetch all students for the dropdown
$stmt = $pdo->query("SELECT id, name, ic_no FROM students ORDER BY name");
$all_students = $stmt->fetchAll();

$report_data = [];

if ($report_type === 'date') {
    // Report by date
    $stmt = $pdo->prepare("
        SELECT s.ic_no, s.name, s.class, a.status, a.notes 
        FROM students s
        LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ?
        ORDER BY s.class, s.name
    ");
    $stmt->execute([$filter_date]);
    $report_data = $stmt->fetchAll();
} else if ($report_type === 'student' && !empty($filter_student)) {
    // Report by student
    $stmt = $pdo->prepare("
        SELECT a.date, a.status, a.notes
        FROM attendance a
        WHERE a.student_id = ?
        ORDER BY a.date DESC
        LIMIT 30
    ");
    $stmt->execute([$filter_student]);
    $report_data = $stmt->fetchAll();

    // Calculate percentages for this student
    $stats = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Total' => count($report_data)];
    foreach ($report_data as $row) {
        if (isset($stats[$row['status']])) {
            $stats[$row['status']]++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Kehadiran Ahli</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        @media print {
            .navbar, .filter-section, .btn {
                display: none !important;
            }
            .container {
                padding: 0;
            }
            .card {
                box-shadow: none;
                border: none;
                padding: 0;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">Pyour<span>Attendance</span></a>
        <div class="nav-links">
            <a href="dashboard.php">Papan Pemuka</a>
            <a href="manage_students.php">Ahli</a>
            <a href="approve_members.php">Kelulusan Ahli</a>
            <a href="mark_attendance.php">Tanda Kehadiran</a>
            <a href="view_reports.php" class="active">Laporan</a>
            <a href="logout.php" class="btn btn-danger" style="padding: 0.5rem 1rem; margin-left: 1rem; color: white;">Log Keluar</a>
        </div>
    </nav>

    <div class="container">

        <div class="card mb-4" style="display: flex; justify-content: space-between; align-items: center; flex-direction: row;">
            <h1 style="font-size: 1.5rem; margin: 0;">Laporan Kehadiran</h1>
            <button onclick="window.print()" class="btn btn-secondary">🖨️ Cetak Laporan</button>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <h2 style="font-size: 1.1rem; margin-bottom: 1.25rem; color: var(--primary-color);">Carian & Penapis</h2>
            <form method="GET" action="" style="display: flex; gap: 2rem; flex-wrap: wrap;">
                
                <div style="flex: 1; min-width: 250px;">
                    <label class="form-label">Jenis Laporan</label>
                    <div style="display: flex; gap: 1.5rem; margin-top: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; color: var(--text-main);">
                            <input type="radio" name="type" value="date" <?= $report_type === 'date' ? 'checked' : '' ?> onchange="this.form.submit()" style="accent-color: var(--primary-color);"> Ringkasan Harian
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; color: var(--text-main);">
                            <input type="radio" name="type" value="student" <?= $report_type === 'student' ? 'checked' : '' ?> onchange="this.form.submit()" style="accent-color: var(--primary-color);"> Individu Ahli
                        </label>
                    </div>
                </div>

                <?php if ($report_type === 'date'): ?>
                    <div style="flex: 2; min-width: 300px; display: flex; gap: 1rem; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label class="form-label" for="date">Pilih Tarikh</label>
                            <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary" style="height: 46px;">Jana Laporan</button>
                    </div>
                <?php else: ?>
                    <div style="flex: 2; min-width: 300px; display: flex; gap: 1rem; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label class="form-label" for="student_id">Pilih Nama Ahli</label>
                            <select id="student_id" name="student_id" class="form-control" required>
                                <option value="">-- Sila pilih ahli --</option>
                                <?php foreach ($all_students as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= $filter_student == $s['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['ic_no']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height: 46px;">Cari Sejarah</button>
                    </div>
                <?php endif; ?>

            </form>
        </div>

        <!-- Report Results -->
        <div class="card">
            <?php if ($report_type === 'date'): ?>
                <!-- Daily Summary Table -->
                <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">Ringkasan Harian untuk <?= date('d F Y', strtotime($filter_date)) ?></h2>
                
                <?php if (count($report_data) > 0): ?>
                    <?php
                        // Quick stats for this day
                        $d_total = count($report_data);
                        $d_present = 0; $d_absent = 0; $d_late = 0; $d_unmarked = 0;
                        foreach ($report_data as $row) {
                        if ($row['status'] === 'Present') $d_present++;
                            elseif ($row['status'] === 'Absent') $d_absent++;
                            elseif ($row['status'] === 'Late') $d_late++;
                            else $d_unmarked++;
                        }
                    ?>
                    
                    <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <span class="badge" style="font-size: 1rem; padding: 0.5rem 1rem; background: var(--bg-color); color: var(--text-main);">Jumlah: <?= $d_total ?></span>
                        <span class="badge badge-present" style="font-size: 1rem; padding: 0.5rem 1rem;">Hadir: <?= $d_present ?></span>
                        <span class="badge badge-absent" style="font-size: 1rem; padding: 0.5rem 1rem;">Tidak Hadir: <?= $d_absent ?></span>
                        <span class="badge badge-late" style="font-size: 1rem; padding: 0.5rem 1rem;">Lewat: <?= $d_late ?></span>
                        <?php if($d_unmarked > 0): ?>
                            <span class="badge" style="font-size: 1rem; padding: 0.5rem 1rem; background: #e5e7eb; color: var(--text-muted);">Belum Ditanda: <?= $d_unmarked ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>No. Kad Pengenalan</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['ic_no']) ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['class']) ?></td>
                                        <td>
                                            <?php if ($row['status']): ?>
                                                <span class="badge badge-<?= strtolower($row['status']) ?>">
                                                    <?= $row['status'] === 'Present' ? 'Hadir' : ($row['status'] === 'Absent' ? 'Tidak Hadir' : 'Lewat') ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-style: italic;">Tiada Rekod</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['notes'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center" style="color: var(--text-muted); padding: 2rem;">Tiada rekod ahli ditemui untuk menjana laporan.</p>
                <?php endif; ?>

            <?php elseif ($report_type === 'student' && !empty($filter_student)): ?>
                <!-- Individual Student Table -->
                
                <?php
                    // Get student details
                    $student_name = "Unknown Student";
                    foreach ($all_students as $s) {
                        if ($s['id'] == $filter_student) {
                            $student_name = $s['name'] . ' (' . $s['ic_no'] . ')';
                            break;
                        }
                    }
                ?>
                
                <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">Sejarah Kehadiran: <?= htmlspecialchars($student_name) ?></h2>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Memaparkan rekod 30 hari terakhir.</p>

                <?php if ($stats['Total'] > 0): ?>
                    <div style="display: flex; gap: 1.5rem; margin-bottom: 1.5rem;">
                        <span class="badge" style="font-size: 1rem; padding: 0.5rem 1rem; background: var(--bg-color); color: var(--text-main);">Jumlah Hari Berrekod: <?= $stats['Total'] ?></span>
                        
                        <?php $p_rate = round((($stats['Present'] + $stats['Late']) / $stats['Total']) * 100); ?>
                        <span class="badge" style="font-size: 1rem; padding: 0.5rem 1rem; background: <?= $p_rate >= 80 ? '#d1fae5' : ($p_rate >= 60 ? '#fef3c7' : '#fee2e2') ?>; color: var(--text-main);">
                            Kadar: <?= $p_rate ?>%
                        </span>
                    </div>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tarikh</th>
                                    <th>Status</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report_data as $row): ?>
                                    <tr>
                                        <td><strong><?= date('d M Y, l', strtotime($row['date'])) ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?= strtolower($row['status']) ?>">
                                                <?= $row['status'] === 'Present' ? 'Hadir' : ($row['status'] === 'Absent' ? 'Tidak Hadir' : 'Lewat') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row['notes'] ?: '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center" style="color: var(--text-muted); padding: 2rem;">Tiada rekod kehadiran ditemui untuk ahli ini.</p>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center" style="padding: 3rem; color: var(--text-muted);">
                    <p>Sila pilih ahli di atas untuk melihat laporan mereka.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
