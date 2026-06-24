<?php
require_once 'config.php';
require_once 'lang.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $sec_q = trim($_POST['security_question']);
    $sec_a = trim($_POST['security_answer']);
    
    if (empty($username) || empty($email) || empty($password) || empty($sec_q) || empty($sec_a)) {
        $error = __('fill_fields');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (strlen($password) < 6) {
        $error = __('password_min');
    } elseif ($password !== $confirm) {
        $error = __('passwords_do_not_match');
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = __('username_exists');
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, security_question, security_answer) VALUES (?, ?, ?, 'viewer', ?, ?)");
            $stmt->bind_param("sssss", $username, $email, $hashed, $sec_q, $sec_a);
            if ($stmt->execute()) {
                $success = __('registration_success');
            } else {
                $error = "Database error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= __('register') ?> | <?= __('app_name') ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: linear-gradient(145deg, #0b2b3b, #1a4a5f);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f0e6d0;
            padding: 2rem;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: rgba(255,255,245,0.1);
            backdrop-filter: blur(12px);
            border-radius: 32px;
            padding: 2rem;
        }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #ffd966; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input {
            width: 100%;
            padding: 10px;
            border-radius: 40px;
            border: none;
            background: rgba(255,255,240,0.2);
            color: white;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #ffb347;
            border: none;
            border-radius: 40px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 1rem;
        }
        .error { color: #ffaaaa; margin-top: 1rem; text-align: center; }
        .success { color: #aaffaa; margin-top: 1rem; text-align: center; }
        .login-link { display: block; text-align: center; margin-top: 1.5rem; color: #ffd966; }
    </style>
</head>
<body>
<div class="container">
    <h2>📝 <?= __('register') ?></h2>
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?> <a href="login.php" style="color:#aaffaa;"><?= __('login') ?></a></div><?php endif; ?>
    <?php if(!$success): ?>
    <form method="POST">
        <div class="form-group">
            <label><?= __('username') ?></label>
            <input type="text" name="username" required>
        </div>
        <div class="form-group">
            <label><?= __('email') ?></label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label><?= __('password') ?></label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label><?= __('confirm_password') ?></label>
            <input type="password" name="confirm_password" required>
        </div>
        <div class="form-group">
            <label><?= __('security_question') ?></label>
            <input type="text" name="security_question" placeholder="<?= __('security_question_placeholder') ?>" required>
        </div>
        <div class="form-group">
            <label><?= __('security_answer_placeholder') ?></label>
            <input type="text" name="security_answer" required>
        </div>
        <button type="submit"><?= __('register') ?></button>
    </form>
    <div class="login-link">
        <a href="login.php"><?= __('already_have_account') ?></a>
    </div>
    <?php endif; ?>
</div>
</body>
</html>