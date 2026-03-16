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
        $success = 'Student deleted successfully.';
    } catch (PDOException $e) {
        $error = 'Failed to delete student (Wait until attendance records are removed or cascade deletes are active).';
    }
}

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $ic_no = trim($_POST['ic_no']);
    $name = trim($_POST['name']);
    $class = trim($_POST['class']);

    if (empty($ic_no) || empty($name) || empty($class)) {
        $error = 'All fields are required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (ic_no, name, class) VALUES (?, ?, ?)");
            $stmt->execute([$ic_no, $name, $class]);
            $success = 'Student added successfully.';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation (Duplicate entry)
                $error = 'A student with this IC Number already exists.';
            } else {
                $error = 'Database error: ' . $e->getMessage();
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
    <title>Manage Students - Student Attendance</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">BADMINTON <span>CLUB</span></a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_students.php" class="active">Students</a>
            <a href="approve_members.php">Kelulusan Ahli</a>
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

        <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr;">
            <!-- Add Student Form -->
            <div class="card">
                <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Add New Student</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label class="form-label" for="ic_no">IC Number</label>
                        <input type="text" id="ic_no" name="ic_no" class="form-control" required placeholder="Enter 12-digit IC No">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="Enter student full name">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="class">Class</label>
                        <input type="text" id="class" name="class" class="form-control" required placeholder="Enter class name">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Add Student</button>
                </form>
            </div>

            <!-- Student List -->
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.25rem;">Approved Student List (<?= count($students) ?>)</h2>
                </div>
                
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>IC Number</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Actions</th>
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
                                            <a href="?delete=<?= $student['id'] ?>" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;" onclick="return confirm('Are you sure you want to delete this student? All their attendance records will also be deleted.');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center" style="color: var(--text-muted); padding: 2rem;">No students registered yet. Fill the form to add one.</td>
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
