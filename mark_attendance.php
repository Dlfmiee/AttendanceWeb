<?php
require_once 'db.php';
checkLogin();

$error = '';
$success = '';

// Default date is today
$selected_date = isset($_GET['date']) ? trim($_GET['date']) : date('Y-m-d');

// Fetch all students
$stmt = $pdo->query("SELECT * FROM students ORDER BY class, name");
$all_students = $stmt->fetchAll();

// Handle form submission (Saving attendance)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_attendance') {
    $attendance_data = $_POST['attendance']; // Array of student_id => status
    $notes_data = $_POST['notes'] ?? []; // Array of student_id => notes
    $submit_date = $_POST['date'];

    try {
        // Begin transaction
        $pdo->beginTransaction();

        foreach ($attendance_data as $student_id => $status) {
            $note = isset($notes_data[$student_id]) ? trim($notes_data[$student_id]) : '';

            // Check if record exists for this date and student
            $check_stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
            $check_stmt->execute([$student_id, $submit_date]);
            $exists = $check_stmt->fetch();

            if ($exists) {
                // Update
                $update_stmt = $pdo->prepare("UPDATE attendance SET status = ?, notes = ? WHERE id = ?");
                $update_stmt->execute([$status, $note, $exists['id']]);
            } else {
                // Insert
                $insert_stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status, notes) VALUES (?, ?, ?, ?)");
                $insert_stmt->execute([$student_id, $submit_date, $status, $note]);
            }
        }

        $pdo->commit();
        $success = "Attendance saved successfully for $submit_date.";
        $selected_date = $submit_date; // Keep selected date active

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error saving attendance: " . $e->getMessage();
    }
}

// Fetch existing attendance records for the selected date to populate the form
$attendance_records = [];
if (!empty($all_students)) {
    $stmt = $pdo->prepare("SELECT student_id, status, notes FROM attendance WHERE date = ?");
    $stmt->execute([$selected_date]);
    while ($row = $stmt->fetch()) {
        $attendance_records[$row['student_id']] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Student Attendance</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .status-radio {
            display: none;
        }
        .status-label {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .status-label.present { border-radius: 6px 0 0 6px; }
        .status-label.absent { }
        .status-label.late { border-radius: 0 6px 6px 0; border-left: none; }
        
        .status-radio:checked + .status-label.present { background: #d1fae5; color: #065f46; border-color: #34d399; z-index: 10; position: relative; }
        .status-radio:checked + .status-label.absent { background: #fee2e2; color: #991b1b; border-color: #f87171; z-index: 10; position: relative; }
        .status-radio:checked + .status-label.late { background: #fef3c7; color: #92400e; border-color: #fbbf24; z-index: 10; position: relative; }

        .radio-group {
            display: flex;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">BADMINTON <span>CLUB</span></a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_students.php">Students</a>
            <a href="approve_members.php">Kelulusan Ahli</a>
            <a href="mark_attendance.php" class="active">Mark Attendance</a>
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

        <div class="card mb-4" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.25rem;">Mark Attendance</h2>
            
            <form method="GET" action="" style="display: flex; gap: 1rem; align-items: center;">
                <label for="date-selector" style="font-weight: 500;">Select Date:</label>
                <input type="date" id="date-selector" name="date" class="form-control" style="width: auto;" value="<?= htmlspecialchars($selected_date) ?>">
                <button type="submit" class="btn btn-secondary">Load Date</button>
            </form>
        </div>

        <div class="card">
            <?php if (count($all_students) > 0): ?>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="save_attendance">
                    <input type="hidden" name="date" value="<?= htmlspecialchars($selected_date) ?>">

                    <div class="table-wrapper mb-4">
                        <table>
                            <thead>
                                <tr>
                                    <th>IC Number</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Notes (Optional)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_students as $student): ?>
                                    <?php 
                                        $sid = $student['id'];
                                        $current_status = isset($attendance_records[$sid]) ? $attendance_records[$sid]['status'] : 'Present'; // Default to present
                                        $current_note = isset($attendance_records[$sid]) ? $attendance_records[$sid]['notes'] : '';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['ic_no']) ?></td>
                                        <td><strong><?= htmlspecialchars($student['name']) ?></strong></td>
                                        <td><span class="badge" style="background: var(--bg-color); color: var(--text-muted);"><?= htmlspecialchars($student['class']) ?></span></td>
                                        <td>
                                            <div class="radio-group">
                                                <input type="radio" class="status-radio" id="present_<?= $sid ?>" name="attendance[<?= $sid ?>]" value="Present" <?= $current_status === 'Present' ? 'checked' : '' ?>>
                                                <label for="present_<?= $sid ?>" class="status-label present">Present</label>

                                                <input type="radio" class="status-radio" id="absent_<?= $sid ?>" name="attendance[<?= $sid ?>]" value="Absent" <?= $current_status === 'Absent' ? 'checked' : '' ?>>
                                                <label for="absent_<?= $sid ?>" class="status-label absent" style="border-left: none; border-right: none;">Absent</label>

                                                <input type="radio" class="status-radio" id="late_<?= $sid ?>" name="attendance[<?= $sid ?>]" value="Late" <?= $current_status === 'Late' ? 'checked' : '' ?>>
                                                <label for="late_<?= $sid ?>" class="status-label late">Late</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="notes[<?= $sid ?>]" class="form-control" placeholder="Add optional note..." value="<?= htmlspecialchars($current_note) ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary" style="font-size: 1.125rem; padding: 1rem 2rem;">Save Attendance for <?= date('d M Y', strtotime($selected_date)) ?></button>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center" style="padding: 3rem; color: var(--text-muted);">
                    <p style="margin-bottom: 1rem;">No students found in the database.</p>
                    <a href="manage_students.php" class="btn btn-primary">Add Students First</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
