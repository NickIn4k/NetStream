<?php
    session_start();

    if (!isset($_SESSION['idUtente']) || !isset($_SESSION['nomeUtente'])) {
        header("Location: /../auth/login.php");
        exit;
    }

    include __DIR__ . '/../includes/header.php';
?>

<h2>Palleee</h2>
<?php 
    include __DIR__ . '/../includes/footer.php';
 ?>