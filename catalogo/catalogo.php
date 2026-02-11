<?php 
    session_start();

    if(isset($_GET['idProfilo']))
        $_SESSION['idProfilo'] = $_GET['idProfilo'];
    else {
        header("Location: /profili/profili.php");
        exit;
    }

    include __DIR__ . '/../includes/header.php';
?>

<h1>Testo di prova</h1>

<?php include __DIR__ . '/../includes/footer.php'; ?>