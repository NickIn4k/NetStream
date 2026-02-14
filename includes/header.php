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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="icon" type="image/png" href="/assets/img/loghi/Logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<header class="floating-header">

    <div class="floating-header-inner">
        <!-- LOGO -->
        <div class="logo-float">
            <a href="/index.php">
                <img src="/assets/img/loghi/Logo.png" alt="NetStream" class="logo-img">
            </a>
        </div>

        <!-- MENU -->
        <div class="menu-float menu-wrapper">

            <div class="menu-icon" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="dropdown-menu" id="menu">
                <?php 
                    if (isset($_SESSION['idUtente']) && isset($_SESSION['nomeUtente'])) {
                        echo "<a href='/profili/profili.php'>Profili</a>";    
                        echo "<a href='/catalogo/catalogo.php'>Catalogo</a>";
                        echo "<a href='/user/dettagliUser.php'>Account</a>";
                        echo "<a href='/auth/logout.php'>Logout</a>";
                    } else {
                        echo "<a href='/auth/login.php'>Login</a>";
                        echo "<a href='/auth/signin.php'>Registrati</a>";
                    }
                ?>
            </div>
        </div>
    </div>
</header>