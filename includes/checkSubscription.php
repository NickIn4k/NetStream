<?php
    if (session_status() === PHP_SESSION_NONE)
        session_start();

    if (!isset($_SESSION['idUtente'])) {
        header("Location: /auth/login.php");
        exit;
    }

    //Se NON attiva --> manda alla pagina dettagli
    if ($_SESSION['statoSottoscrizione'] !== 'ATTIVA') {
        header("Location: /user/dettagliUser.php");
        exit;
    }
?>