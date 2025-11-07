<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

function greetUser($name) {
    $h = date("H");
    if ($h < 12) return "Good morning, $name 🌤️";
    if ($h < 18) return "Good afternoon, $name ☀️";
    return "Good evening, $name 🌙";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PalmVest Dashboard</title>

<!-- Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Dashboard Styles -->
<link rel="stylesheet" href="./assets/dash.css">

</head>

<body class="pv-body">

<!-- SIDEBAR -->
<aside class="pv-sidebar" id="sidebar">
    <div class="pv-brand">PalmVest</div>

    <nav class="pv-nav">
        <a href="?page=overview" class="active"><i class="bi bi-speedometer2"></i> Overview</a>
        <a href="?page=buy_oil"><i class="bi bi-basket"></i> Buy Oil</a>
        <a href="?page=portfolio"><i class="bi bi-briefcase-fill"></i> Portfolio</a>
        <a href="?page=transactions"><i class="bi bi-receipt"></i> Transactions</a>
        <a href="?page=wallet"><i class="bi bi-wallet2"></i> Wallet</a>
        <a href="?page=referrals"><i class="bi bi-people-fill"></i> Referrals</a>
        <a href="../../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="pv-main">

    <!-- TOPBAR -->
    <header class="pv-topbar">

        <!-- Left: Hamburger -->
        <button id="sidebarToggle" class="pv-hamburger">
            <i class="bi bi-list"></i>
        </button>

        <!-- Center: Greeting -->
        <div class="pv-greeting text-center flex-grow-1">
            <div class="fw-bold"><?= greetUser($_SESSION['user_name']); ?></div>
            <div class="small text-light">PalmVest Dashboard</div>
        </div>

        <!-- Right: Avatar -->
        <div class="pv-avatar-wrap">
            <img src="https://i.pravatar.cc/50?u=<?= $_SESSION['user_id']; ?>" 
                alt="profile" class="pv-avatar">
        </div>

    </header>

    <!-- CONTENT AREA -->
    <section class="pv-content">
        <?php
            $page = $_GET['page'] ?? 'overview';
            $file = __DIR__ . "/sections/$page.php";

            if (file_exists($file)) {
                include $file;
            } else {
                echo "<div class='text-light p-3'>Page not found.</div>";
            }
        ?>
    </section>

</main>

<script src="./assets/dash.js"></script>
</body>
</html>
