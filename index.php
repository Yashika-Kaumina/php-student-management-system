<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'lang.php';
require_once 'profile_helper.php';

$lang = $_SESSION['lang'] ?? 'si';
$t = $lang_data[$lang];
$isAdmin = ($_SESSION['role'] ?? 'viewer') === 'admin';

// Get profile image
$user_id = $_SESSION['user_id'];
$profile_img = getProfileImage($user_id, $conn);

// Create (admin only)
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $class = $_POST['class'];
    $conn->query("INSERT INTO students (name, age, email, class) VALUES ('$name', '$age', '$email', '$class')");
    header("Location: index.php");
    exit;
}

// Delete (admin only)
if ($isAdmin && isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM students WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Edit - show form (admin only)
if ($isAdmin && isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $student = $conn->query("SELECT * FROM students WHERE id=$id")->fetch_assoc();
}

// Update (admin only)
if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $class = $_POST['class'];
    $conn->query("UPDATE students SET name='$name', age='$age', email='$email', class='$class' WHERE id=$id");
    header("Location: index.php");
    exit;
}

// Search logic (everyone)
$search = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $result = $conn->query("SELECT * FROM students WHERE name LIKE '%$search%' OR class LIKE '%$search%'");
} else {
    $result = $conn->query("SELECT * FROM students");
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($t['app_name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: linear-gradient(145deg, #0b2b3b, #1a4a5f);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f0e6d0;
            padding: 2rem;
        }
        .container {
            max-width: 1400px;
            margin: auto;
            background: rgba(255,255,245,0.1);
            backdrop-filter: blur(12px);
            border-radius: 32px;
            padding: 2rem;
        }
        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .lang-switch select {
            background: rgba(0,0,0,0.4);
            border: 1px solid #ffd966;
            color: #ffefc0;
            padding: 8px 12px;
            border-radius: 30px;
            cursor: pointer;
            font-family: inherit;
        }
        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }
        .profile-dropdown img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ffd966;
            cursor: pointer;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: #1e2f3a;
            min-width: 180px;
            border-radius: 16px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
            z-index: 1;
            overflow: hidden;
            border: 1px solid rgba(255,215,0,0.5);
        }
        .dropdown-menu a {
            color: #f0e6d0;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: 0.2s;
        }
        .dropdown-menu a:hover {
            background-color: rgba(255,215,0,0.2);
            color: #ffd966;
        }
        .show {
            display: block;
        }
        h1 {
            text-align: center;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffd89b, #c7e9fb);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .search-form {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .search-form input {
            padding: 10px 18px;
            border-radius: 40px;
            border: none;
            background: rgba(255,255,240,0.2);
            color: white;
            width: 260px;
        }
        .search-form button, .search-form a {
            padding: 10px 18px;
            border-radius: 40px;
            border: none;
            background: #ffb347;
            color: #1a2a3a;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }
        .search-form a {
            background: #555;
            color: white;
        }
        .crud-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .crud-form input, .crud-form button {
            padding: 10px 18px;
            border-radius: 40px;
            border: none;
            font-size: 1rem;
        }
        .crud-form input {
            background: rgba(255,255,240,0.2);
            color: white;
        }
        .crud-form button {
            background: #ffb347;
            cursor: pointer;
            font-weight: bold;
        }
        .crud-form a {
            color: #ffd966;
            text-decoration: none;
            align-self: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,200,0.2);
        }
        th {
            background: rgba(255,215,0,0.3);
        }
        .actions a {
            margin-right: 12px;
            text-decoration: none;
            color: #ffd966;
        }
        .delete {
            color: #ff8888;
        }
        .no-data {
            text-align: center;
            padding: 20px;
        }
        @media (max-width: 700px) {
            body { padding: 1rem; }
            th, td { font-size: 0.8rem; }
            .top-bar { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Top Bar: Left Language Switcher, Right Profile Dropdown -->
    <div class="top-bar">
        <div class="lang-switch">
            <form method="GET" style="display: inline;">
                <select name="lang" onchange="this.form.submit()">
                    <option value="si" <?= $lang=='si' ? 'selected' : '' ?>>සිංහල</option>
                    <option value="en" <?= $lang=='en' ? 'selected' : '' ?>>English</option>
                    <option value="ta" <?= $lang=='ta' ? 'selected' : '' ?>>தமிழ்</option>
                </select>
            </form>
        </div>
        <div class="profile-dropdown">
            <img src="uploads/<?= htmlspecialchars($profile_img) ?>" alt="Profile" id="profileImg">
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="dashboard.php">📊 <?= htmlspecialchars($t['dashboard']) ?></a>
                <a href="change_password.php">🔑 <?= htmlspecialchars($t['change_password']) ?></a>
                <a href="profile.php">🖼️ <?= htmlspecialchars($t['change_profile_pic'] ?? 'Change Profile Picture') ?></a>
                <a href="logout.php">🚪 <?= htmlspecialchars($t['logout']) ?></a>
            </div>
        </div>
    </div>

    <h1>📚 <?= htmlspecialchars($t['app_name']) ?></h1>

    <!-- Search Bar -->
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="<?= htmlspecialchars($t['search_placeholder']) ?>" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit">🔍 <?= htmlspecialchars($t['search_btn']) ?></button>
        <?php if (isset($_GET['search']) && $_GET['search'] != ''): ?>
            <a href="index.php">❌ <?= htmlspecialchars($t['clear']) ?></a>
        <?php endif; ?>
    </form>

    <!-- Add / Edit Form (Admin only) -->
    <?php if ($isAdmin): ?>
    <form method="POST" class="crud-form">
        <input type="hidden" name="id" value="<?= isset($student) ? $student['id'] : '' ?>">
        <input type="text" name="name" placeholder="<?= htmlspecialchars($t['name']) ?>" value="<?= isset($student) ? htmlspecialchars($student['name']) : '' ?>" required>
        <input type="number" name="age" placeholder="<?= htmlspecialchars($t['age']) ?>" value="<?= isset($student) ? $student['age'] : '' ?>" required>
        <input type="email" name="email" placeholder="<?= htmlspecialchars($t['email']) ?>" value="<?= isset($student) ? htmlspecialchars($student['email']) : '' ?>" required>
        <input type="text" name="class" placeholder="<?= htmlspecialchars($t['class']) ?>" value="<?= isset($student) ? htmlspecialchars($student['class']) : '' ?>" required>
        <?php if (isset($student)): ?>
            <button type="submit" name="update">✏️ <?= htmlspecialchars($t['edit']) ?></button>
            <a href="index.php">❌ <?= htmlspecialchars($t['cancel'] ?? 'Cancel') ?></a>
        <?php else: ?>
            <button type="submit" name="add">➕ <?= htmlspecialchars($t['add_student']) ?></button>
        <?php endif; ?>
    </form>
    <?php endif; ?>

    <!-- Students Table -->
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th><?= htmlspecialchars($t['name']) ?></th>
                    <th><?= htmlspecialchars($t['age']) ?></th>
                    <th><?= htmlspecialchars($t['email']) ?></th>
                    <th><?= htmlspecialchars($t['class']) ?></th>
                    <?php if ($isAdmin): ?>
                    <th><?= htmlspecialchars($t['actions']) ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['age'] ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['class']) ?></td>
                        <?php if ($isAdmin): ?>
                        <td class="actions">
                            <a href="?edit=<?= $row['id'] ?>">✏️ <?= htmlspecialchars($t['edit']) ?></a>
                            <a href="?delete=<?= $row['id'] ?>" class="delete" onclick="return confirm('<?= htmlspecialchars($t['confirm_delete']) ?>')">🗑️ <?= htmlspecialchars($t['delete']) ?></a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="<?= $isAdmin ? 6 : 5 ?>" class="no-data"><?= htmlspecialchars($t['no_students']) ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Dropdown toggle
    const profileImg = document.getElementById('profileImg');
    const dropdown = document.getElementById('dropdownMenu');
    profileImg.addEventListener('click', function(event) {
        event.stopPropagation();
        dropdown.classList.toggle('show');
    });
    window.addEventListener('click', function() {
        dropdown.classList.remove('show');
    });
</script>
</body>
</html>