<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ic_no = preg_replace('/[^0-9]/', '', $_POST['ic_no']);
    $name = trim($_POST['name']);
    $class = trim($_POST['class']);

    if (empty($ic_no) || empty($name) || empty($class)) {
        header("Location: register.php?error=" . urlencode("Sila isi semua maklumat."));
        exit;
    }

    if (strlen($ic_no) !== 12) {
        header("Location: register.php?error=" . urlencode("Nombor Kad Pengenalan mestilah 12 digit."));
        exit;
    }

    try {
        // Check if student already exists
        $stmt = $pdo->prepare("SELECT id FROM students WHERE ic_no = ?");
        $stmt->execute([$ic_no]);
        
        if ($stmt->fetch()) {
            header("Location: register.php?error=" . urlencode("Nombor IC ini sudah berdaftar sebagai ahli."));
            exit;
        }

        // Insert new student with Pending status explicitly (though it's default)
        $stmt = $pdo->prepare("INSERT INTO students (ic_no, name, class, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->execute([$ic_no, $name, $class]);

        // Redirect to index with success message instructing them to wait
        $success_msg = "Pendaftaran berjaya! Sila tunggu kelulusan Admin sebelum anda boleh mendaftar kehadiran.";
        header("Location: index.php?success=1&name=" . urlencode($success_msg));
        exit;

    } catch (PDOException $e) {
        header("Location: register.php?error=" . urlencode("Ralat sistem: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: register.php");
    exit;
}
