<?php
$conn = new mysqli("localhost", "root", "", "student_clean_db");
$result = $conn->query("SELECT username, password FROM users");
while($row = $result->fetch_assoc()) {
    echo $row['username'] . " - " . $row['password'] . "<br>";
    if (password_verify("viewer123", $row['password'])) {
        echo "✅ viewer123 matches for " . $row['username'] . "<br>";
    } else {
        echo "❌ viewer123 does NOT match<br>";
    }
}
?>