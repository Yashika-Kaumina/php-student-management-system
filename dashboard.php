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
$user_id = $_SESSION['user_id'];
$profile_img = getProfileImage($user_id, $conn);

// Statistics
$total = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$avg_age = round($conn->query("SELECT AVG(age) as avg FROM students")->fetch_assoc()['avg'], 1);

// For chart
$chart_labels = [];
$chart_counts = [];
$class_stats = $conn->query("SELECT class, COUNT(*) as count FROM students GROUP BY class");
while($row = $class_stats->fetch_assoc()) {
    $chart_labels[] = $row['class'];
    $chart_counts[] = $row['count'];
}
$labels_json = json_encode($chart_labels);
$counts_json = json_encode($chart_counts);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($t['dashboard']) ?> | <?= htmlspecialchars($t['app_name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            background: linear-gradient(145deg, #0b2b3b, #1a4a5f);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f0e6d0;
            padding: 2rem;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: rgba(255,255,245,0.1);
            backdrop-filter: blur(12px);
            border-radius: 32px;
            padding: 2rem;
        }
        /* Top bar with language switcher and profile dropdown */
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
        /* Profile dropdown same as index.php */
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
        .show { display: block; }
        h1 {
            text-align: center;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ffd89b, #c7e9fb);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .stats {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }
        .card {
            background: rgba(0,0,0,0.3);
            border-radius: 28px;
            padding: 1.5rem;
            text-align: center;
            min-width: 180px;
            border: 1px solid rgba(255,215,0,0.3);
        }
        .card h2 { font-size: 2.5rem; color: #ffd966; }
        .card p { font-size: 1.1rem; }
        .chart-container {
            background: rgba(0,0,0,0.2);
            border-radius: 28px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        canvas { max-height: 300px; margin: 0 auto; }
        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: #ffb347;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        @media (max-width: 700px) {
            .card { min-width: 140px; padding: 1rem; }
            .card h2 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Top Bar: Left Language, Right Profile Dropdown -->
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
                <a href="index.php">📋 <?= htmlspecialchars($t['app_name']) ?></a>
                <a href="change_password.php">🔑 <?= htmlspecialchars($t['change_password']) ?></a>
                <a href="profile.php">🖼️ <?= htmlspecialchars($t['change_profile_pic'] ?? 'Change Profile Picture') ?></a>
                <a href="logout.php">🚪 <?= htmlspecialchars($t['logout']) ?></a>
            </div>
        </div>
    </div>

    <h1>📊 <?= htmlspecialchars($t['dashboard']) ?></h1>

    <div class="stats">
        <div class="card">
            <h2><?= $total ?></h2>
            <p>👨‍🎓 <?= htmlspecialchars($t['total_students']) ?></p>
        </div>
        <div class="card">
            <h2><?= $avg_age ?></h2>
            <p>📅 <?= htmlspecialchars($t['avg_age']) ?></p>
        </div>
    </div>

    <div class="chart-container">
        <h3 style="text-align:center; margin-bottom:1rem;">📊 <?= htmlspecialchars($t['class_stats']) ?></h3>
        <canvas id="classChart" width="400" height="200"></canvas>
    </div>

    <a href="index.php" class="back-link">🔙 <?= htmlspecialchars($t['back']) ?></a>
</div>

<script>
    const ctx = document.getElementById('classChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= $labels_json ?>,
            datasets: [{
                label: '<?= htmlspecialchars($t['students']) ?>',
                data: <?= $counts_json ?>,
                backgroundColor: 'rgba(255, 215, 0, 0.6)',
                borderColor: 'rgba(255, 215, 0, 1)',
                borderWidth: 1,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { labels: { color: '#f0e6d0' } } },
            scales: {
                y: { beginAtZero: true, ticks: { color: '#f0e6d0' }, grid: { color: 'rgba(255,255,200,0.2)' } },
                x: { ticks: { color: '#f0e6d0' }, grid: { color: 'rgba(255,255,200,0.2)' } }
            }
        }
    });

    // Dropdown toggle
    const profileImg = document.getElementById('profileImg');
    const dropdown = document.getElementById('dropdownMenu');
    profileImg.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });
    window.addEventListener('click', function() {
        dropdown.classList.remove('show');
    });
</script>
</body>
</html>