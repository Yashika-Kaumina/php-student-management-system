<?php
require_once 'config.php';
require_once 'lang.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/";
    $file_name = time() . '_' . basename($_FILES['profile_image']['name']);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Validate file type
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed)) {
        $message = "Only JPG, JPEG, PNG, GIF allowed.";
    } elseif ($_FILES['profile_image']['size'] > 2000000) {
        $message = "File too large (max 2MB).";
    } else {
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Delete old profile image if not default
            $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $old = $stmt->get_result()->fetch_assoc()['profile_image'];
            if ($old && $old != 'default.png' && file_exists($target_dir . $old)) {
                unlink($target_dir . $old);
            }
            // Update database
            $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->bind_param("si", $file_name, $user_id);
            $stmt->execute();
            $message = "Profile picture updated!";
        } else {
            $message = "Upload failed.";
        }
    }
}

// Get user data
$stmt = $conn->prepare("SELECT username, email, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$profile_image = $user['profile_image'] ?? 'default.png';
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{background:linear-gradient(145deg,#0b2b3b,#1a4a5f);font-family:'Segoe UI',sans-serif;color:#f0e6d0;padding:2rem;}
        .profile-card{max-width:500px;margin:50px auto;background:rgba(255,255,245,0.1);backdrop-filter:blur(12px);border-radius:32px;padding:2rem;text-align:center;}
        img{width:150px;height:150px;border-radius:50%;object-fit:cover;border:3px solid #ffd966;margin-bottom:1rem;}
        input, button{margin:10px 0;padding:10px;border-radius:30px;border:none;}
        button{background:#ffb347;cursor:pointer;font-weight:bold;}
        .message{color:#aaffaa;}
        .back-link{display:inline-block;margin-top:1rem;color:#ffd966;}
    </style>
</head>
<body>
<div class="profile-card">
    <h2>👤 <?= $_SESSION['username'] ?></h2>
    <img src="uploads/<?= htmlspecialchars($profile_image) ?>" alt="Profile Picture">
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <?php if($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="profile_image" accept="image/*" required>
        <button type="submit">📸 Upload New Picture</button>
    </form>
    <a href="index.php" class="back-link">← Back to Dashboard</a>
</div>
</body>
</html>