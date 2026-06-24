<?php
$conn = new mysqli("localhost", "root", "", "student_clean_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$new_hash = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "UPDATE users SET password = '$new_hash' WHERE username = 'admin'";

if ($conn->query($sql) === TRUE) {
    echo "✅ Admin password reset successful!<br>";
    echo "New password: <strong>admin123</strong><br>";
} else {
    echo "❌ Error: " . $conn->error;
}

$conn->close();
echo "<br><a href='login.php'>🔐 Go to Login</a>";
?>