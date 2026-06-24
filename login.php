<?php
require_once 'config.php';
require_once 'lang.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                header("Location: index.php");
                exit;
            } else {
                $error = __('wrong_password');
            }
        } else {
            $error = __('user_not_found');
        }
    } else {
        $error = __('fill_fields');
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('login') ?> | <?= __('app_name') ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: linear-gradient(145deg, #0b2b3b, #1a4a5f);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(255,255,245,0.1);
            backdrop-filter: blur(16px);
            border-radius: 48px;
            padding: 40px 35px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,215,0,0.3);
            transition: transform 0.3s;
        }
        .login-card:hover { transform: translateY(-5px); }
        h2 { text-align: center; color: #ffd966; margin-bottom: 25px; font-size: 2rem; }
        .lang-switch { text-align: right; margin-bottom: 15px; }
        .lang-switch select {
            background: rgba(0,0,0,0.4);
            border: 1px solid #ffd966;
            color: #ffefc0;
            padding: 5px 10px;
            border-radius: 20px;
            cursor: pointer;
            font-family: inherit;
        }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; color: #ffefc0; font-weight: 500; }
        .input-group input {
            width: 100%;
            padding: 12px 18px;
            background: rgba(255,255,240,0.08);
            border: 1px solid rgba(255,215,0,0.4);
            border-radius: 40px;
            font-size: 1rem;
            color: white;
            transition: 0.3s;
        }
        .input-group input:focus {
            outline: none;
            border-color: #ffb347;
            background: rgba(255,255,240,0.15);
            box-shadow: 0 0 8px rgba(255,180,70,0.3);
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(95deg, #ffb347, #ff8c1a);
            border: none;
            border-radius: 40px;
            font-size: 1.1rem;
            font-weight: bold;
            color: #1a2a3a;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover { transform: scale(1.02); box-shadow: 0 0 15px rgba(255,140,26,0.5); }
        .error {
            background: rgba(220,50,50,0.2);
            border-left: 4px solid #ff6b6b;
            padding: 12px;
            border-radius: 16px;
            margin-bottom: 20px;
            color: #ffc5c5;
            font-size: 0.9rem;
        }
        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .links a {
            color: #ffd966;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .demo {
            text-align: center;
            margin-top: 15px;
            font-size: 0.75rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="lang-switch">
        <form method="GET" style="display: inline;">
            <select name="lang" onchange="this.form.submit()">
                <option value="si" <?= $lang=='si' ? 'selected' : '' ?>>සිංහල</option>
                <option value="en" <?= $lang=='en' ? 'selected' : '' ?>>English</option>
                <option value="ta" <?= $lang=='ta' ? 'selected' : '' ?>>தமிழ்</option>
            </select>
        </form>
    </div>
    <h2>🔐 <?= __('login') ?></h2>
    <?php if ($error): ?>
        <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
        <div class="input-group">
            <label>👤 <?= __('username') ?></label>
            <input type="text" name="username" placeholder="admin" autocomplete="off" required autofocus>
        </div>
        <div class="input-group">
            <label>🔒 <?= __('password') ?></label>
            <input type="password" name="password" placeholder="••••••••" autocomplete="off" required>
        </div>
        <button type="submit">🚀 <?= __('login') ?></button>
    </form>
    <div class="links">
        <a href="forgot_password.php">❓ <?= __('forgot_password') ?></a>
        <a href="register.php">📝 <?= __('register') ?></a>
    </div>
    <div class="demo">
        demo: admin / admin123
    </div>
</div>
</body>
</html>