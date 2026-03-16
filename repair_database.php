<?php
// repair_database.php
// This script will automatically create the database tables and reset the admin password.

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'attendance_db';

echo "<h1>🛠️ Database Repair Tool</h1>";

try {
    // 1. Connect without database first
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` COLLATE utf8mb4_general_ci");
    echo "<p style='color: green;'>✔ Database `$dbname` is ready.</p>";
    
    // 3. Select the database
    $pdo->exec("USE `$dbname` ;");
    
    // 4. Create Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(50) NOT NULL,
      `password` varchar(255) NOT NULL,
      `name` varchar(100) NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "<p style='color: green;'>✔ 'users' table is ready.</p>";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DROP TABLE IF EXISTS `attendance` ");
    $pdo->exec("DROP TABLE IF EXISTS `students` ");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 5. Create Students Table
    $pdo->exec("CREATE TABLE `students` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ic_no` varchar(20) NOT NULL,
      `name` varchar(100) NOT NULL,
      `class` varchar(50) NOT NULL,
      `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `ic_no` (`ic_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "<p style='color: green;'>✔ 'students' table is ready (updated to IC No).</p>";

    // 6. Create Attendance Table
    $pdo->exec("CREATE TABLE `attendance` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `student_id` int(11) NOT NULL,
      `date` date NOT NULL,
      `status` enum('Present','Absent','Late') NOT NULL,
      `notes` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `student_date` (`student_id`,`date`),
      KEY `date` (`date`),
      CONSTRAINT `fk_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "<p style='color: green;'>✔ 'attendance' table is ready.</p>";

    // 7. Create Settings Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `setting_key` varchar(50) NOT NULL,
      `setting_value` text DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // Check if settings already exist, if not insert defaults
    $check_stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($check_stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES 
            ('activity_name', 'Latihan Mingguan Badminton'),
            ('activity_location', 'Dewan Sukan Keningau')");
    }
    echo "<p style='color: green;'>✔ 'settings' table is ready.</p>";

    // 8. Reset/Create Admin User
    $admin_user = 'admin';
    $admin_pass = 'password123';
    $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);
    
    // Delete existing admin to be sure
    $pdo->prepare("DELETE FROM users WHERE username = ?")->execute([$admin_user]);
    
    // Insert fresh admin
    $stmt = $pdo->prepare("INSERT INTO users (username, password, name) VALUES (?, ?, ?)");
    $stmt->execute([$admin_user, $hashed_pass, 'Administrator']);
    
    echo "<div style='background: #ecfdf5; border: 1px solid #059669; padding: 1rem; border-radius: 8px; margin-top: 1rem;'>";
    echo "<h2 style='color: #065f46; margin-top: 0;'>✅ Success! Admin Reset</h2>";
    echo "<p>The database has been repaired and the admin user has been reset.</p>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> password123</p>";
    echo "<p><a href='index.php' style='display: inline-block; background: #059669; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; margin-top: 0.5rem;'>Go to Login Page</a></p>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #fef2f2; border: 1px solid #dc2626; padding: 1rem; border-radius: 8px; margin-top: 1rem;'>";
    echo "<h2 style='color: #991b1b; margin-top: 0;'>❌ Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Troubleshooting:</strong> Make sure XAMPP Control Panel is open and <strong>MySQL</strong> is started (green).</p>";
    echo "</div>";
}
?>
