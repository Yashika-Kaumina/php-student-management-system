<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

// Get all students
$result = $conn->query("SELECT * FROM students ORDER BY id");

// HTML content for PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student List</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h1 { text-align: center; color: #1a4a5f; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #999; padding: 8px; text-align: left; }
        th { background: #ffd966; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #555; }
    </style>
</head>
<body>
    <h1>Student Management System - Student List</h1>
    <table>
        <thead>
            <tr><th>ID</th><th>Name</th><th>Age</th><th>Email</th><th>Class</th></tr>
        </thead>
        <tbody>';

while($row = $result->fetch_assoc()) {
    $html .= '<tr>
        <td>'.$row['id'].'</td>
        <td>'.htmlspecialchars($row['name']).'</td>
        <td>'.$row['age'].'</td>
        <td>'.htmlspecialchars($row['email']).'</td>
        <td>'.htmlspecialchars($row['class']).'</td>
    </tr>';
}

$html .= '
        </tbody>
    </table>
    <div class="footer">Generated on ' . date('Y-m-d H:i:s') . '</div>
</body>
</html>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("students_list.pdf", array("Attachment" => false));
exit;
?>