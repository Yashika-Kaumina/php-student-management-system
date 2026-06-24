<?php
$conn = new mysqli("localhost", "root", "", "student_clean_db");
$new_hash = password_hash("viewer123", PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$new_hash' WHERE username = 'viewer'");
echo "✅ Viewer password updated to 'viewer123'<br>";
echo "<a href='login.php'>Go to Login</a>";
?>