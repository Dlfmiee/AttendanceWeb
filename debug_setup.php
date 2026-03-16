<?php
// debug_setup.php
require_once 'db.php';

echo "<h1>Database Debug Setup</h1>";

try {
    // 1. Check Connection
    echo "<p style='color: green;'>✔ Database Connection Successful!</p>";

    // 2. Check if Database exists
    $stmt = $pdo->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    echo "<p>Current Database: <strong>$dbName</strong></p>";

    // 3. Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✔ 'users' table found.</p>";
        
        // 4. Check for admin user
        $stmt = $pdo->query("SELECT * FROM users WHERE username = 'admin'");
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p style='color: green;'>✔ 'admin' user found in database.</p>";
            
            // 5. Verify password hash for 'password123'
            $test_pass = 'password123';
            if (password_verify($test_pass, $user['password'])) {
                echo "<p style='color: green;'>✔ Password 'password123' verified successfully against stored hash.</p>";
            } else {
                echo "<p style='color: red;'>✘ Password verification FAILED. The hash in the database does not match 'password123'.</p>";
                
                // 6. Offer a fix
                $new_hash = password_hash('password123', PASSWORD_DEFAULT);
                echo "<p>To fix this, go to phpMyAdmin and run this SQL query:</p>";
                echo "<code>UPDATE users SET password = '$new_hash' WHERE username = 'admin';</code>";
            }
        } else {
            echo "<p style='color: red;'>✘ 'admin' user NOT found. Did you import the database.sql file?</p>";
        }
    } else {
        echo "<p style='color: red;'>✘ 'users' table NOT found. Please import the database.sql file in phpMyAdmin.</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>✘ Error: " . $e->getMessage() . "</p>";
    echo "<p>Common issues: <br>1. XAMPP MySQL is not started.<br>2. The database 'attendance_db' has not been created in phpMyAdmin.</p>";
}
?>
<hr>
<p><a href="index.php">Go back to Login Page</a></p>
