<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>NetStream</title>

    <!-- PATH ASSOLUTO -->
    <link rel="stylesheet" href="/Progetti/NetStream/assets/css/style.css">
</head>
<body>

<header>
    <nav>
        <div class="logo">
            <a href="/Progetti/NetStream/index.php">
                <img src="/Progetti/NetStream/assets/img/loghi/LogoScritta.png" alt="NetStream" class="logo-img">
            </a>
        </div>

        <div class="nav-links">
            <?php if (isset($_SESSION['idProfilo'])): ?>
                <a href="/Progetti/NetStream/catalogo/catalogo.php">Catalogo</a>
                <a href="/Progetti/NetStream/user/dettagliUser.php">Account</a>
                <a href="/Progetti/NetStream/auth/logout.php">Logout</a>
            <?php else: ?>
                <a href="/Progetti/NetStream/auth/login.php">Login</a>
                <a href="/Progetti/NetStream/auth/signin.php">Registrati</a>
            <?php endif; ?>
        </div>
    </nav>
</header>