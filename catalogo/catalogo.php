<?php 

    session_start();

    if(!isset($_SESSION['profilo'])) {
        header("Location: /../profili/profili.php");
        exit;
    }
?>