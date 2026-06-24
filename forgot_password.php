<?php
require_once 'config.php';
require_once 'lang.php';

$step = 1; // 1=email, 2=security, 3=reset
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check_email'])) {
        $email = trim($_POST['email']);
        $stmt = $conn->prepare("SELECT id, username, security_question FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $_SESSION['reset_user_id'] = $row['id'];
            $_SESSION['reset_username'] = $row['username'];
            $_SESSION['reset_question'] = $row['security_question'];
            $step = 2;
        } else {
            $error = "Email not found";
        }
    } 
    elseif (isset($_POST['verify_answer'])) {
        $answer = trim($_POST['answer']);
        $stmt = $conn->prepare("SELECT security_answer FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['reset_user_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if (strtolower($answer) === strtolower($row['security_answer'])) {
            $step = 3;
        } else {
            $error = "Wrong answer";
        }
    }
    elseif (isset($_POST['reset_password'])) {
        $newpass = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        if (strlen($newpass) < 6) {
            $error = "Password must be at least 6 characters";
        } elseif ($newpass !== $confirm) {
            $error = "Passwords do not match";
        } else {
            $hashed = password_hash($newpass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $_SESSION['reset_user_id']);
            if ($stmt->execute()) {
                $success = "Password reset successfully! <a href='login.php'>Login now</a>";
                unset($_SESSION['reset_user_id'], $_SESSION['reset_username'], $_SESSION['reset_question']);
                $step = 0;
            } else {
                $error = "Error, try again";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container { max-width: 500px; margin: 50px auto; background: rgba(255,255,245,0.1); backdrop-filter: blur(12px); border-radius: 32px; padding: 2rem; }
        .form-group { margin-bottom: 15px; }
        input, button { width: 100%; padding: 10px; border-radius: 40px; border: none; }
        button { background: #ffb347; cursor: pointer; font-weight: bold; }
        .error { color: #ffaaaa; }
        .success { color: #aaffaa; }
    </style>
</head>
<body>
<div class="container">
    <h2>🔑 Forgot Password</h2>
    <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    
    <?php if($step == 1 && !$success): ?>
    <form method="POST">
        <div class="form-group"><input type="email" name="email" placeholder="Your Email" required></div>
        <button type="submit" name="check_email">Next</button>
    </form>
    <?php elseif($step == 2): ?>
    <form method="POST">
        <p><strong><?= htmlspecialchars($_SESSION['reset_question']) ?></strong></p>
        <div class="form-group"><input type="text" name="answer" placeholder="Your Answer" required></div>
        <button type="submit" name="verify_answer">Verify</button>
    </form>
    <?php elseif($step == 3): ?>
    <form method="POST">
        <div class="form-group"><input type="password" name="new_password" placeholder="New Password" required></div>
        <div class="form-group"><input type="password" name="confirm_password" placeholder="Confirm Password" required></div>
        <button type="submit" name="reset_password">Reset Password</button>
    </form>
    <?php endif; ?>
    <p><a href="login.php">Back to Login</a></p>
</div>
</body>
</html>