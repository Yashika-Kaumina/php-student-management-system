<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['lang'])) $_SESSION['lang'] = 'si';
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
    $redirect = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: $redirect");
    exit;
}
$lang = $_SESSION['lang'];

$conn = new mysqli("localhost", "root", "", "student_clean_db");
if ($conn->connect_error) die("Connection failed");
$conn->set_charset("utf8");
?>