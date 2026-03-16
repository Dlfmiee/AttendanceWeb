<?php
// db.php - Database Connection file

$host = 'localhost';
$dbname = 'attendance_db';
$username = 'root'; // Default XAMPP username
$password = ''; // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Fetch objects by default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "<br>Please ensure XAMPP MySQL is running and you have imported the database.sql file.");
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to check if user is logged in
function checkLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}
?>
