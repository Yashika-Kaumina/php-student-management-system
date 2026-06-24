<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'lang.php';
$lang = $_SESSION['lang'] ?? 'si';
$t = $lang_data[$lang];

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!password_verify($current, $user['password'])) {
        $error = $t['incorrect_current'];
    } elseif (strlen($new) < 6) {
        $error = $t['password_too_short'];
    } elseif ($new !== $confirm) {
        $error = $t['passwords_do_not_match'];
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $new_hash, $user_id);
        if ($update->execute()) $success = $t['password_changed'];
        else $error = "DB error";
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $t['change_password'] ?></title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:linear-gradient(145deg,#0b2b3b,#1a4a5f);font-family:'Segoe UI',sans-serif;color:#f0e6d0;padding:2rem;}
        .container{max-width:500px;margin:50px auto;background:rgba(255,255,245,0.1);backdrop-filter:blur(12px);border-radius:32px;padding:2rem;}
        h2{text-align:center;margin-bottom:1.5rem;color:#ffd966;}
        .form-group{margin-bottom:1rem;}
        label{display:block;margin-bottom:0.5rem;}
        input{width:100%;padding:10px;border-radius:40px;border:none;background:rgba(255,255,240,0.2);color:white;}
        button{width:100%;padding:10px;background:#ffb347;border:none;border-radius:40px;font-weight:bold;cursor:pointer;margin-top:1rem;}
        .error{color:#ffaaaa;text-align:center;margin-top:1rem;}
        .success{color:#aaffaa;text-align:center;margin-top:1rem;}
        .back-link{display:block;text-align:center;margin-top:1.5rem;color:#ffd966;}
    </style>
</head>
<body>
<div class="container">
    <h2>🔐 <?= htmlspecialchars($t['change_password']) ?></h2>
    <?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group"><label><?= htmlspecialchars($t['current_password']) ?></label><input type="password" name="current_password" required></div>
        <div class="form-group"><label><?= htmlspecialchars($t['new_password']) ?></label><input type="password" name="new_password" required></div>
        <div class="form-group"><label><?= htmlspecialchars($t['confirm_password']) ?></label><input type="password" name="confirm_password" required></div>
        <button type="submit"><?= htmlspecialchars($t['update_password']) ?></button>
    </form>
    <a href="index.php" class="back-link">← <?= htmlspecialchars($t['back']) ?></a>
</div>
</body>
</html>