<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ic_no = trim($_POST['ic_no']);
    
    // Clean IC number (remove dashes or spaces if user typed them)
    $clean_ic = preg_replace('/[^0-9]/', '', $ic_no);

    if (empty($clean_ic)) {
        header("Location: index.php?error=Please enter a valid IC number.");
        exit();
    }

    // 1. Find the student
    $stmt = $pdo->prepare("SELECT id, name, status FROM students WHERE ic_no = ? OR ic_no = ?");
    $stmt->execute([$clean_ic, $ic_no]); // Try both cleaned and original
    $student = $stmt->fetch();

    if (!$student) {
        $msg = "Maaf, maklumat anda tidak ditemui. Sila daftar sebagai ahli Kelab Badminton terlebih dahulu.";
        header("Location: index.php?error=" . urlencode($msg));
        exit();
    }

    if ($student['status'] === 'Pending') {
        $msg = "Akaun anda sedang menunggu kelulusan Admin. Sila cuba lagi nanti.";
        header("Location: index.php?error=" . urlencode($msg));
        exit();
    }

    if ($student['status'] === 'Rejected') {
        $msg = "Maaf, permohonan keahlian anda telah ditolak. Sila berhubung dengan Admin.";
        header("Location: index.php?error=" . urlencode($msg));
        exit();
    }

    $student_id = $student['id'];
    $student_name = $student['name'];
    $today = date('Y-m-d');

    // 2. Check if already marked for today
    $stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
    $stmt->execute([$student_id, $today]);
    
    if ($stmt->fetch()) {
        header("Location: index.php?error=You have already marked your attendance for today!");
        exit();
    }

    // 3. Mark as Present
    try {
        $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status, notes) VALUES (?, ?, 'Present', 'Self Checked-in')");
        $stmt->execute([$student_id, $today]);
        
        header("Location: index.php?success=1&name=" . urlencode($student_name));
        exit();
    } catch (PDOException $e) {
        header("Location: index.php?error=Database error. Please try again later.");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
