<?php 

    session_start();

    if(!isset($_SESSION['idProfilo'])) {
        header("Location: /profili/profili.php");
        exit;
    }
?>